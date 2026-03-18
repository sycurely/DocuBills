<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoiceCustomReminder;
use App\Models\InvoiceEmailConfiguration;
use App\Models\InvoiceReminderTemplateBinding;
use App\Models\Tax;
use App\Services\EmailService;
use App\Services\InvoiceValidationContract;
use App\Services\ReminderRuleService;
use App\Services\SettingService;
use App\Services\TemplateResolutionService;
use App\Services\TaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        if (!has_permission('view_invoices') && !has_permission('view_all_invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Invoice::with(['client', 'creator']);

        // Apply ownership filter unless user has 'view_all_invoices' permission
        if (!has_permission('view_all_invoices')) {
            $query->where('created_by', Auth::id());
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('bill_to_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $clients = Client::whereNull('deleted_at')
            ->orderBy('company_name')
            ->get();

        $lineTaxes = Tax::lineLevel()->get();
        $invoiceTaxes = Tax::invoiceLevel()->orderedByCalcOrder()->get();

        $defaultCurrency = SettingService::getSetting('currency_code', 'USD');
        $currencySymbol = SettingService::getSetting('currency_symbol', '$');

        $zipAvailable = class_exists(\ZipArchive::class);

        return view('invoices.create', compact('clients', 'lineTaxes', 'invoiceTaxes', 'defaultCurrency', 'currencySymbol', 'zipAvailable'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $activeReminderRuleIds = collect(ReminderRuleService::getRules())
            ->filter(fn (array $rule) => !empty($rule['enabled']) && !empty($rule['id']))
            ->pluck('id')
            ->values()
            ->all();

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'bill_to' => 'required|array',
            'bill_to.Company Name' => 'required|string|max:255',
            'bill_to.Email' => 'nullable|email|max:255',
            'bill_to.Phone' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'invoice_time' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|string',
            'include_due_time' => 'nullable|boolean',
            'currency_code' => 'required|string|max:10',
            'currency_display' => 'nullable|string|max:10',
            'invoice_title_bg' => 'nullable|string|max:7',
            'invoice_title_text' => 'nullable|string|max:7',
            'show_bank_details' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.rate' => 'required|numeric|min:0',
            'line_items.*.tax_id' => [
                'nullable',
                'integer',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tax_type', 'line')),
            ],
            'invoice_tax_ids' => 'nullable|array',
            'invoice_tax_ids.*' => [
                'integer',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tax_type', 'invoice')),
            ],
            'delivery_template_id' => 'nullable|integer|exists:email_templates,id',
            'payment_confirmation_template_id' => 'nullable|integer|exists:email_templates,id',
            'reminder_bindings' => 'nullable|array',
            'reminder_bindings.*.rule_id' => ['required_with:reminder_bindings', 'string', 'max:64', Rule::in($activeReminderRuleIds)],
            'reminder_bindings.*.template_id' => 'nullable|integer|exists:email_templates,id',
            'reminder_date' => 'nullable|date|after_or_equal:invoice_date',
            'reminder_days_after' => 'nullable|integer|min:0|max:365',
        ]);

        $hasReminderDate = !empty($validated['reminder_date'] ?? null);
        $hasReminderDays = array_key_exists('reminder_days_after', $validated)
            && $validated['reminder_days_after'] !== null
            && $validated['reminder_days_after'] !== '';
        if ($hasReminderDate && $hasReminderDays) {
            return back()->withErrors([
                'reminder_date' => 'Select either a reminder date or days after due date (not both).',
            ])->withInput();
        }

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($validated['bill_to']['Company Name']);

        // Handle dates
        $invoiceDate = $validated['invoice_date'];
        if ($validated['invoice_time'] ?? null) {
            $invoiceDate .= ' ' . $validated['invoice_time'];
        }

        $dueDate = null;
        if ($validated['due_date'] ?? null) {
            $dueDate = $validated['due_date'];
            if (($validated['include_due_time'] ?? false) && ($validated['due_time'] ?? null)) {
                $dueDate .= ' ' . $validated['due_time'];
            }
        } else {
            // Default: 14 days from invoice date
            $dueDate = date('Y-m-d', strtotime($invoiceDate . ' +14 days'));
        }

        // Handle client
        $clientId = $validated['client_id'] ?? null;
        if (!$clientId) {
            // Create or update client
            $client = Client::firstOrCreate(
                ['company_name' => $validated['bill_to']['Company Name']],
                [
                    'representative' => $validated['bill_to']['Contact Name'] ?? null,
                    'email' => $validated['bill_to']['Email'] ?? null,
                    'phone' => $validated['bill_to']['Phone'] ?? null,
                    'address' => $validated['bill_to']['Address'] ?? null,
                    'gst_hst' => $validated['bill_to']['gst_hst'] ?? null,
                    'notes' => $validated['bill_to']['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );

            // Update existing client if found
            if ($client->wasRecentlyCreated === false) {
                $client->update([
                    'representative' => $validated['bill_to']['Contact Name'] ?? $client->representative,
                    'email' => $validated['bill_to']['Email'] ?? $client->email,
                    'phone' => $validated['bill_to']['Phone'] ?? $client->phone,
                    'address' => $validated['bill_to']['Address'] ?? $client->address,
                    'gst_hst' => $validated['bill_to']['gst_hst'] ?? $client->gst_hst,
                    'notes' => $validated['bill_to']['notes'] ?? $client->notes,
                ]);
            }

            $clientId = $client->id;
        }

        // Recurring invoice logic
        $isRecurring = ($validated['is_recurring'] ?? false) && has_permission('manage_recurring_invoices');
        $recurrenceType = $isRecurring ? 'monthly' : null;
        $nextRunDate = null;
        if ($isRecurring) {
            $nextRunDate = date('Y-m-d', strtotime($invoiceDate . ' +1 month'));
        }

        // Normalize line items and calculate totals
        $lineItemsInput = array_map(function (array $item): array {
            $item['tax_id'] = TaxService::sanitizeLineTaxId($item['tax_id'] ?? null);
            return $item;
        }, $validated['line_items']);

        [$lineItems, $subtotal, $lineTaxTotal, $lineTaxLines] = $this->normalizeLineItems($lineItemsInput);
        $invoiceTaxIds = TaxService::sanitizeInvoiceTaxIds((array) ($validated['invoice_tax_ids'] ?? []));
        [$invoiceTaxLines, $invoiceTaxTotal] = $this->calculateInvoiceTaxes($invoiceTaxIds, $subtotal + $lineTaxTotal);
        $totalAmount = round($subtotal + $lineTaxTotal + $invoiceTaxTotal, 2);

        $currencyDisplay = $validated['currency_display'] ?? $validated['currency_code'];
        $titleBg = $validated['invoice_title_bg'] ?? '#FFDC00';
        $titleText = $validated['invoice_title_text'] ?? '#0033D9';
        $taxSummary = $this->buildTaxSummary(
            true,
            $subtotal,
            $lineTaxLines,
            $invoiceTaxLines,
            $invoiceTaxTotal
        );

        $invoiceDateFormatted = Carbon::parse($invoiceDate)->format('Y-m-d');
        $dueDateFormatted = $dueDate ? Carbon::parse($dueDate)->format('Y-m-d') : 'N/A';
        $showBankDetails = (bool) ($validated['show_bank_details'] ?? true);
        $renderPayload = $this->buildInvoiceRenderPayload(
            $invoiceNumber,
            $validated['bill_to'],
            $lineItems,
            $subtotal,
            $lineTaxTotal,
            $invoiceTaxLines,
            $taxSummary,
            $totalAmount,
            $validated['currency_code'],
            $currencyDisplay,
            $invoiceDateFormatted,
            $dueDateFormatted,
            $titleBg,
            $titleText,
            $showBankDetails,
            '',
            'Unpaid'
        );
        $invoiceHtml = $this->renderInvoiceHtml($renderPayload);

        // Create invoice
        $invoicePayload = [
            'invoice_number' => $invoiceNumber,
            'client_id' => $clientId,
            'bill_to_name' => $validated['bill_to']['Company Name'],
            'bill_to_json' => $validated['bill_to'],
            'total_amount' => $totalAmount,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'status' => 'Unpaid',
            'html' => $invoiceHtml,
            'created_by' => Auth::id(),
            'show_bank_details' => $showBankDetails,
            'is_recurring' => $isRecurring,
            'recurrence_type' => $recurrenceType,
            'next_run_date' => $nextRunDate,
            'currency_code' => $validated['currency_code'],
            'currency_display' => $currencyDisplay,
            'invoice_title_bg' => $titleBg,
            'invoice_title_text' => $titleText,
        ];
        if ($this->invoiceTaxSummaryColumnExists()) {
            $invoicePayload['invoice_tax_summary'] = $taxSummary;
        }
        $invoice = Invoice::create($invoicePayload);

        $this->persistInvoiceEmailConfiguration(
            $invoice,
            $validated['delivery_template_id'] ?? null,
            $validated['payment_confirmation_template_id'] ?? null,
            $this->normalizeReminderBindings((array) ($validated['reminder_bindings'] ?? []))
        );
        $this->persistCustomReminderSchedule(
            $invoice,
            $validated['reminder_date'] ?? null,
            $hasReminderDays ? (int) $validated['reminder_days_after'] : null
        );

        // Create Stripe payment link if applicable
        if (!($request->input('skip_stripe') ?? false)) {
            $this->createPaymentLink($invoice);
        }
        $invoice->refresh();

        $this->renderAndPersistInvoiceDocument($invoice, $this->buildInvoiceRenderPayload(
            $invoiceNumber,
            $validated['bill_to'],
            $lineItems,
            $subtotal,
            $lineTaxTotal,
            $invoiceTaxLines,
            $taxSummary,
            $totalAmount,
            $validated['currency_code'],
            $currencyDisplay,
            $invoiceDateFormatted,
            $dueDateFormatted,
            $titleBg,
            $titleText,
            $showBankDetails,
            (string) ($invoice->payment_link ?? ''),
            (string) ($invoice->status ?? 'Unpaid')
        ));
        $this->generatePdf($invoice);

        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
        EmailService::sendInvoiceEmail($invoice, $pdfPath);

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        if (!has_permission('view_invoices') && !has_permission('view_all_invoices')) {
            abort(403, 'Unauthorized action.');
        }

        // Enforce ownership unless 'view_all_invoices' permission
        if (!has_permission('view_all_invoices') && $invoice->created_by !== Auth::id()) {
            abort(404);
        }

        $invoice->load(['client', 'creator']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Generate invoice number based on client name.
     */
    private function generateInvoiceNumber(string $clientName): string
    {
        $prefix = SettingService::getSetting('invoice_prefix', 'INV');

        // Clean and split client name
        $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', strtoupper($clientName));
        $words = preg_split('/\s+/', trim($cleanName));

        // Build client code
        if (count($words) === 1) {
            $clientCode = substr($words[0], 0, 3);
        } else {
            $limit = min(count($words), 4);
            $clientCode = '';
            for ($i = 0; $i < $limit; $i++) {
                $clientCode .= substr($words[$i], 0, 1);
            }
        }

        // Get next sequence number (database-agnostic parsing).
        $likePattern = "{$prefix}-{$clientCode}-%";
        $existingNumbers = Invoice::where('invoice_number', 'like', $likePattern)
            ->pluck('invoice_number');

        $maxSeq = 0;
        foreach ($existingNumbers as $number) {
            $parts = explode('-', (string) $number);
            $tail = end($parts);
            if ($tail !== false && ctype_digit((string) $tail)) {
                $maxSeq = max($maxSeq, (int) $tail);
            }
        }

        $nextSeq = (int) $maxSeq + 1;

        return sprintf('%s-%s-%02d', $prefix, $clientCode, $nextSeq);
    }

    /**
     * Generate PDF for invoice.
     */
    private function generatePdf(Invoice $invoice): void
    {
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($invoice->html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfOutput = $dompdf->output();

            // Save PDF
            $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
            Storage::disk('invoices')->put($pdfPath, $pdfOutput);

            // Save HTML
            $htmlPath = "invoices/{$invoice->invoice_number}.html";
            Storage::disk('invoices')->put($htmlPath, $invoice->html);

        } catch (\Exception $e) {
            \Log::error("PDF generation failed for invoice {$invoice->invoice_number}: " . $e->getMessage());
        }
    }

    /**
     * Create Stripe payment link for invoice.
     */
    private function createPaymentLink(Invoice $invoice): void
    {
        try {
            $testMode = SettingService::getSetting('test_mode', '0') === '1';
            $stripeSecret = SettingService::getSetting('stripe_secret_key');
            $stripePublic = SettingService::getSetting('stripe_publishable_key');

            $hasCurl = extension_loaded('curl') || function_exists('curl_version');

            if ($stripeSecret && $stripePublic && class_exists(Stripe::class) && $hasCurl) {
                Stripe::setApiKey($stripeSecret);

                $stripeCurrency = strtolower($invoice->currency_code);
                $stripeSupported = ['cad', 'usd', 'eur', 'gbp', 'aud', 'aed', 'sar'];

                if (!in_array($stripeCurrency, $stripeSupported, true) || $invoice->total_amount > 999999.99) {
                    $paymentLink = null;
                    $paymentProvider = 'Manual';
                } else {
                    $unitAmount = (int) round($invoice->total_amount * 100);

                    $session = Session::create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency' => $stripeCurrency,
                                'unit_amount' => $unitAmount,
                                'product_data' => [
                                    'name' => 'Invoice #' . $invoice->invoice_number
                                ]
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'success_url' => url("/payment-success?invoice={$invoice->id}"),
                        'cancel_url' => url("/invoices/{$invoice->id}"),
                        'metadata' => [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                        ]
                    ]);

                    $paymentLink = $session->url;
                    $paymentProvider = $testMode ? 'Test' : 'Stripe';
                }
            } else {
                $paymentLink = null;
                $paymentProvider = 'Manual';
            }

            $invoice->update([
                'payment_link' => $paymentLink,
                'payment_provider' => $paymentProvider,
            ]);

        } catch (\Throwable $e) {
            \Log::error("Payment link creation failed for invoice {$invoice->invoice_number}: " . $e->getMessage());
            $invoice->update([
                'payment_link' => null,
                'payment_provider' => 'Manual',
            ]);
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        if (!has_permission('mark_invoice_paid')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'payment_method' => ['required', Rule::in(['Cheque', 'Direct Debit', 'Bank Transfer', 'Cash'])],
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $wasPaid = strtolower((string) $invoice->status) === 'paid';
        $invoice->update(['status' => 'Paid']);
        $invoice->refresh();
        $this->refreshInvoiceDocument($invoice);

        if (!$wasPaid) {
            EmailService::sendPaymentConfirmationEmail($invoice);
        }

        return redirect()->back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        if (!has_permission('download_invoice_pdf')) {
            abort(403, 'Unauthorized action.');
        }

        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
        $hasPayNowInHtml = str_contains((string) ($invoice->html ?? ''), 'class="pay-now-button"');
        $hasDisabledPayNowInHtml = str_contains((string) ($invoice->html ?? ''), 'pay-now-button-disabled');
        $needsPayNowSync = strtolower((string) ($invoice->status ?? '')) === 'unpaid'
            && (!$hasPayNowInHtml || (!empty($invoice->payment_link) && $hasDisabledPayNowInHtml));
        $needsPaidSync = strtolower((string) ($invoice->status ?? '')) === 'paid'
            && $hasPayNowInHtml;

        if (!Storage::disk('invoices')->exists($pdfPath) || $needsPayNowSync || $needsPaidSync) {
            $this->refreshInvoiceDocument($invoice);
        }

        return Storage::disk('invoices')->download($pdfPath, "{$invoice->invoice_number}.pdf");
    }

    /**
     * Handle Stripe payment success callback.
     */
    public function paymentSuccess(Request $request)
    {
        $invoiceId = (int) $request->query('invoice', 0);
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return redirect()->route('invoices.index')->with('error', 'Invoice not found.');
        }

        $this->markInvoicePaid($invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment received. Invoice marked as paid.');
    }

    /**
     * Stripe webhook endpoint.
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');
        $endpointSecret = (string) SettingService::getSetting('stripe_webhook_secret');

        if ($endpointSecret === '') {
            return response('Webhook secret not configured', 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $invoiceId = (int) ($session->metadata->invoice_id ?? 0);
            $invoiceNumber = (string) ($session->metadata->invoice_number ?? '');

            $invoice = $invoiceId ? Invoice::find($invoiceId) : null;
            if (!$invoice && $invoiceNumber !== '') {
                $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
            }

            if ($invoice) {
                $this->markInvoicePaid($invoice);
            }
        }

        return response('Webhook received', 200);
    }

    private function markInvoicePaid(Invoice $invoice): void
    {
        $wasPaid = strtolower((string) $invoice->status) === 'paid';
        if (!$wasPaid) {
            $invoice->update(['status' => 'Paid']);
            $invoice->refresh();
        }

        $this->refreshInvoiceDocument($invoice);

        if (!$wasPaid) {
            EmailService::sendPaymentConfirmationEmail($invoice);
        }
    }

    /**
     * Import invoices from Excel/CSV.
     */
    public function import(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension() ?? '');
                    if (!in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
                        $fail(InvoiceValidationContract::MSG_ALLOWED_UPLOAD_TYPES);
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension() ?? '');
        if ($ext === 'xlsx' && !class_exists('ZipArchive')) {
            $this->logFlowValidationFailure('invoice_import', 'zip_required', ['ext' => $ext]);
            return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_ZIP_REQUIRED);
        }
        $rows = $this->loadSpreadsheetRowsFromUploadedFile($file);
        if ($rows === null) {
            $this->logFlowValidationFailure('invoice_import', 'no_file_rows');
            return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_NO_FILE_ROWS);
        }

        $created = $this->createInvoicesFromRows($rows);

        return redirect()->route('invoices.index')->with('success', "Imported {$created} invoice(s).");
    }

    /**
     * Import invoices from selected source (reference-style flow: Google Sheet URL or file upload).
     */
    public function importFromSource(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'invoice_source' => 'required|in:google,upload',
            'google_sheet_url' => 'nullable|string|max:2048',
            'file' => 'nullable|file',
            'bill_to' => 'required|array',
            'bill_to.Company Name' => 'required|string|max:255',
            'bill_to.Email' => 'required|email|max:255',
            'bill_to.Phone' => 'nullable|string|max:50',
            'bill_to.Contact Name' => 'nullable|string|max:255',
            'bill_to.Address' => 'nullable|string|max:500',
        ]);

        if ($validated['invoice_source'] === 'upload') {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension() ?? '');
                        if (!in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
                            $fail(InvoiceValidationContract::MSG_ALLOWED_UPLOAD_TYPES);
                        }
                    },
                ],
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension() ?? '');
            if ($ext === 'xlsx' && !class_exists('ZipArchive')) {
                $this->logFlowValidationFailure('invoice_import_source', 'zip_required', ['source' => 'upload', 'ext' => $ext]);
                return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_ZIP_REQUIRED);
            }

            $rows = $this->loadSpreadsheetRowsFromUploadedFile($file);
            if ($rows === null) {
                $this->logFlowValidationFailure('invoice_import_source', 'no_file_rows', ['source' => 'upload']);
                return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_NO_FILE_ROWS);
            }
        } else {
            $request->validate([
                'google_sheet_url' => 'required|url',
            ]);

            $csvExportUrl = $this->toGoogleCsvExportUrl((string) $request->input('google_sheet_url'));
            if ($csvExportUrl === null) {
                $this->logFlowValidationFailure('invoice_import_source', 'invalid_google_url', ['source' => 'google']);
                return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_INVALID_GOOGLE_URL);
            }

            $response = Http::timeout(20)->get($csvExportUrl);
            if (!$response->successful()) {
                $this->logFlowValidationFailure('invoice_import_source', 'google_fetch_failed', [
                    'source' => 'google',
                    'status' => $response->status(),
                ]);
                return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_GOOGLE_FETCH_FAILED);
            }

            $rows = $this->parseCsvRowsToSheetShape($response->body());
            if (count($rows) < 2) {
                $this->logFlowValidationFailure('invoice_import_source', 'no_google_rows', ['source' => 'google']);
                return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_NO_GOOGLE_ROWS);
            }
        }

        $this->prepareImportSessionData($request, $rows, $validated['bill_to']);
        $prepared = $request->session()->get(InvoiceValidationContract::SESSION_IMPORT);
        if (empty($prepared['headers']) || empty($prepared['items'])) {
            $this->logFlowValidationFailure('invoice_import_source', 'no_usable_rows');
            return redirect()->back()->with(InvoiceValidationContract::FLASH_IMPORT_ERROR, InvoiceValidationContract::MSG_NO_USABLE_ROWS);
        }

        return redirect()->route('invoices.price-select');
    }

    /**
     * Show reference-style price selection step before invoice generation.
     */
    public function showPriceSelect(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->session()->get(InvoiceValidationContract::SESSION_IMPORT);
        if (!$data || empty($data['headers']) || empty($data['items'])) {
            $this->logFlowValidationFailure('invoice_price_select_show', 'import_session_missing');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_NO_IMPORT_DATA);
        }

        $headers = (array) ($data['headers'] ?? []);
        $recommendedPriceColumn = $this->pickRecommendedPriceColumn($headers);
        $recommendedIncludeCols = $this->pickRecommendedIncludeCols($headers, $recommendedPriceColumn);

        return view('invoices.price-select', [
            'headers' => $headers,
            'items' => $data['items'],
            'billTo' => $data['bill_to'],
            'defaultCurrencyCode' => strtoupper((string) SettingService::getSetting('currency_code', 'USD')),
            'currencyOptions' => $this->getGenerateCurrencyOptions(),
            'recommendedPriceColumn' => $recommendedPriceColumn,
            'recommendedIncludeCols' => $recommendedIncludeCols,
        ]);
    }

    /**
     * Save price selection and move to generate-invoice page.
     */
    public function savePriceSelect(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->session()->get(InvoiceValidationContract::SESSION_IMPORT);
        if (!$data || empty($data['headers']) || empty($data['items'])) {
            $this->logFlowValidationFailure('invoice_price_select_save', 'import_session_missing');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_EXPIRED);
        }

        $headers = $data['headers'];

        $validated = $request->validate([
            'price_mode' => 'required|in:' . InvoiceValidationContract::PRICE_MODE_VALIDATION_LIST,
            'price_column' => 'nullable|string',
            'include_cols' => 'required|array|min:' . InvoiceValidationContract::INCLUDE_COLS_MIN . '|max:' . InvoiceValidationContract::INCLUDE_COLS_MAX,
            'include_cols.*' => 'string',
            'currency_code' => 'nullable|string|size:3',
        ], [
            'include_cols.required' => InvoiceValidationContract::MSG_INCLUDE_COLS_MIN,
            'include_cols.array' => InvoiceValidationContract::MSG_INCLUDE_COLS_MIN,
            'include_cols.min' => InvoiceValidationContract::MSG_INCLUDE_COLS_MIN,
            'include_cols.max' => InvoiceValidationContract::MSG_INCLUDE_COLS_MAX,
        ]);

        $priceMode = InvoiceValidationContract::normalizePriceMode((string) ($validated['price_mode'] ?? ''));
        if ($priceMode === null) {
            $this->logFlowValidationFailure('invoice_price_select_save', 'invalid_price_mode');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_EXPIRED);
        }
        $priceColumn = (string) ($validated['price_column'] ?? '');
        $includeCols = array_values(array_intersect((array) ($validated['include_cols'] ?? []), $headers));
        if (count($includeCols) < InvoiceValidationContract::INCLUDE_COLS_MIN) {
            $this->logFlowValidationFailure('invoice_price_select_save', 'include_cols_min', ['count' => count($includeCols)]);
            return back()->withErrors(['include_cols' => InvoiceValidationContract::MSG_INCLUDE_COLS_MIN])->withInput();
        }
        if (count($includeCols) > InvoiceValidationContract::INCLUDE_COLS_MAX) {
            $this->logFlowValidationFailure('invoice_price_select_save', 'include_cols_max', ['count' => count($includeCols)]);
            return back()->withErrors(['include_cols' => InvoiceValidationContract::MSG_INCLUDE_COLS_MAX])->withInput();
        }

        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC && ($priceColumn === '' || !in_array($priceColumn, $headers, true))) {
            $this->logFlowValidationFailure('invoice_price_select_save', 'invalid_price_column', ['price_column' => $priceColumn]);
            return back()->withErrors(['price_column' => InvoiceValidationContract::MSG_INVALID_PRICE_COLUMN])->withInput();
        }

        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC) {
            if (!in_array($priceColumn, $includeCols, true)) {
                if (count($includeCols) >= InvoiceValidationContract::INCLUDE_COLS_MAX) {
                    array_pop($includeCols);
                }
                $includeCols[] = $priceColumn;
            }
            $lineItemsInput = $this->buildAutomaticLineItemsInput((array) ($data['items'] ?? []), $includeCols, $priceColumn);
            if (empty($lineItemsInput)) {
                $this->logFlowValidationFailure('invoice_price_select_save', 'no_valid_priced_rows', ['price_column' => $priceColumn]);
                return back()->withErrors(['price_column' => InvoiceValidationContract::MSG_NO_VALID_PRICED_ROWS])->withInput();
            }
        }

        $currencyCode = strtoupper((string) ($validated['currency_code'] ?? SettingService::getSetting('currency_code', 'USD')));
        $currencyOptions = $this->getGenerateCurrencyOptions();
        if (!array_key_exists($currencyCode, $currencyOptions)) {
            $currencyCode = strtoupper((string) SettingService::getSetting('currency_code', 'USD'));
            if (!array_key_exists($currencyCode, $currencyOptions)) {
                $currencyCode = 'USD';
            }
        }

        $request->session()->put(InvoiceValidationContract::SESSION_PRICE_CONFIG, [
            'include_cols' => $includeCols,
            'price_mode' => $priceMode,
            'price_column' => $priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC ? $priceColumn : null,
            'currency_code' => $currencyCode,
        ]);

        return redirect()->route('invoices.generate');
    }

    /**
     * Show generate page after price-select (reference flow).
     */
    public function showGenerateFromImport(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->session()->get(InvoiceValidationContract::SESSION_IMPORT);
        $config = $request->session()->get(InvoiceValidationContract::SESSION_PRICE_CONFIG);
        if (!$data || !$config) {
            $this->logFlowValidationFailure('invoice_generate_show', 'session_missing');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_SESSION_EXPIRED);
        }

        $priceMode = InvoiceValidationContract::normalizePriceMode((string) ($config['price_mode'] ?? ''));
        if ($priceMode === null) {
            $this->logFlowValidationFailure('invoice_generate_show', 'invalid_price_mode');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_SESSION_EXPIRED);
        }
        $priceColumn = (string) ($config['price_column'] ?? '');
        $includeCols = (array) ($config['include_cols'] ?? []);
        $currencyCode = strtoupper((string) ($config['currency_code'] ?? SettingService::getSetting('currency_code', 'USD')));
        $currencyOptions = $this->getGenerateCurrencyOptions();
        if (!array_key_exists($currencyCode, $currencyOptions)) {
            $currencyCode = 'USD';
        }
        $headers = (array) ($data['headers'] ?? []);
        $includeCols = array_values(array_intersect($includeCols, $headers));
        if (empty($includeCols)) {
            $includeCols = array_slice($headers, 0, InvoiceValidationContract::INCLUDE_COLS_MAX);
        }
        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC && $priceColumn !== '' && in_array($priceColumn, $headers, true) && !in_array($priceColumn, $includeCols, true)) {
            if (count($includeCols) >= InvoiceValidationContract::INCLUDE_COLS_MAX) {
                array_pop($includeCols);
            }
            $includeCols[] = $priceColumn;
        }
        $items = (array) ($data['items'] ?? []);

        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC) {
            if ($priceColumn === '' || !in_array($priceColumn, $headers, true)) {
                $this->logFlowValidationFailure('invoice_generate_show', 'invalid_price_column', ['price_column' => $priceColumn]);
                return redirect()->route('invoices.price-select')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_RESELECT_PRICING_COLUMN);
            }
            $lineItemsInput = $this->buildAutomaticLineItemsInput($items, $includeCols, $priceColumn);
            if (empty($lineItemsInput)) {
                $this->logFlowValidationFailure('invoice_generate_show', 'no_valid_priced_rows', ['price_column' => $priceColumn]);
                return redirect()->route('invoices.price-select')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_NO_VALID_PRICED_ROWS);
            }
            $previewTotal = array_sum(array_map(fn (array $row) => (float) $row['rate'], $lineItemsInput));
        } else {
            $lineItemsInput = $this->buildManualLineItemsInput($items, $includeCols);
            $previewTotal = null;
        }

        $tableRows = $this->buildPreviewTableRows($items, $includeCols, $priceColumn, $priceMode);
        $lineTaxes = Tax::lineLevel()->get(['id', 'name', 'percentage']);
        $invoiceTaxes = Tax::invoiceLevel()->orderedByCalcOrder()->get(['id', 'name', 'percentage', 'calc_order']);
        $emailTemplates = EmailTemplate::query()
            ->whereNull('deleted_at')
            ->orderBy('template_name')
            ->get(['id', 'template_name']);
        $reminderRules = collect(ReminderRuleService::getRules())
            ->filter(fn (array $rule) => !empty($rule['id']) && !empty($rule['enabled']))
            ->map(function (array $rule): array {
                $rule['label'] = $this->formatReminderRuleLabel($rule);
                return $rule;
            })
            ->values()
            ->all();
        $defaultReminderBindings = $this->buildDefaultReminderBindings($reminderRules);
        $defaultReminderTemplateMap = [];
        foreach ($defaultReminderBindings as $binding) {
            $ruleId = (string) ($binding['rule_id'] ?? '');
            $templateId = (int) ($binding['template_id'] ?? 0);
            if ($ruleId !== '' && $templateId > 0) {
                $defaultReminderTemplateMap[$ruleId] = $templateId;
            }
        }
        $canEditInvoice = has_permission('edit_invoice');
        $canManageRecurring = has_permission('manage_recurring_invoices');

        return view('invoices.generate', [
            'billTo' => $data['bill_to'] ?? [],
            'priceMode' => $priceMode,
            'priceColumn' => $priceColumn,
            'includeCols' => $includeCols,
            'currencyCode' => $currencyCode,
            'currencyOptions' => $currencyOptions,
            'tableRows' => $tableRows,
            'previewTotal' => $previewTotal,
            'initialInvoiceDate' => now()->format('Y-m-d'),
            'initialDueDate' => now()->addDays(14)->format('Y-m-d'),
            'company' => $this->getCompanyInfo(),
            'lineTaxes' => $lineTaxes,
            'invoiceTaxes' => $invoiceTaxes,
            'emailTemplates' => $emailTemplates,
            'reminderRules' => $reminderRules,
            'defaultReminderBindings' => $defaultReminderBindings,
            'defaultReminderTemplateMap' => $defaultReminderTemplateMap,
            'canEditInvoice' => $canEditInvoice,
            'canManageRecurring' => $canManageRecurring,
            'bankDefaults' => $this->getInvoiceBankingDefaults(),
            'initialTitleBg' => '#FFDC00',
        ]);
    }

    /**
     * Save invoice from generate page.
     */
    public function saveGenerateFromImport(Request $request)
    {
        if (!has_permission('create_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->session()->get(InvoiceValidationContract::SESSION_IMPORT);
        $config = $request->session()->get(InvoiceValidationContract::SESSION_PRICE_CONFIG);
        if (!$data || !$config) {
            $this->logFlowValidationFailure('invoice_generate_save', 'session_missing');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_SESSION_EXPIRED);
        }

        $priceMode = InvoiceValidationContract::normalizePriceMode((string) ($config['price_mode'] ?? ''));
        if ($priceMode === null) {
            $this->logFlowValidationFailure('invoice_generate_save', 'invalid_price_mode');
            return redirect()->route('invoices.create')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_IMPORT_SESSION_EXPIRED);
        }
        $priceColumn = (string) ($config['price_column'] ?? '');
        $includeCols = (array) ($config['include_cols'] ?? []);
        $currencyCode = strtoupper((string) ($config['currency_code'] ?? SettingService::getSetting('currency_code', 'USD')));
        $currencyOptions = $this->getGenerateCurrencyOptions();
        if (!array_key_exists($currencyCode, $currencyOptions)) {
            $currencyCode = 'USD';
        }
        $headers = (array) ($data['headers'] ?? []);
        $includeCols = array_values(array_intersect($includeCols, $headers));
        if (empty($includeCols)) {
            $includeCols = array_slice($headers, 0, InvoiceValidationContract::INCLUDE_COLS_MAX);
        }
        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC && $priceColumn !== '' && in_array($priceColumn, $headers, true) && !in_array($priceColumn, $includeCols, true)) {
            if (count($includeCols) >= InvoiceValidationContract::INCLUDE_COLS_MAX) {
                array_pop($includeCols);
            }
            $includeCols[] = $priceColumn;
        }
        $items = (array) ($data['items'] ?? []);
        $activeReminderRuleIds = collect(ReminderRuleService::getRules())
            ->filter(fn (array $rule) => !empty($rule['enabled']) && !empty($rule['id']))
            ->pluck('id')
            ->values()
            ->all();
        $request->validate([
            'preview_include_cols' => 'nullable|array',
            'preview_include_cols.*' => 'string',
        ]);
        $requestedPreviewCols = $request->input('preview_include_cols', []);
        if (!is_array($requestedPreviewCols)) {
            $requestedPreviewCols = [];
        }
        $activeIncludeCols = array_values(array_intersect($includeCols, $requestedPreviewCols));
        if (empty($activeIncludeCols)) {
            $activeIncludeCols = $includeCols;
        }
        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC && $priceColumn !== '' && !in_array($priceColumn, $activeIncludeCols, true)) {
            $activeIncludeCols[] = $priceColumn;
        }
        $validatedDates = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency_code' => 'nullable|string|size:3',
            'is_recurring' => 'nullable|boolean',
            'show_bank_details' => 'nullable|boolean',
            'bank_account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_iban' => 'nullable|string|max:255',
            'bank_swift' => 'nullable|string|max:255',
            'bank_routing_code' => 'nullable|string|max:255',
            'bank_payment_instructions' => 'nullable|string|max:1000',
            'taxable_invoice' => 'nullable|boolean',
            'line_tax_ids' => 'nullable|array',
            'line_tax_matrix' => 'nullable|string',
            'line_tax_matrix_mode' => 'nullable|boolean',
            'line_tax_ids.*' => [
                'integer',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tax_type', 'line')),
            ],
            'invoice_tax_ids' => 'nullable|array',
            'invoice_tax_ids.*' => [
                'integer',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tax_type', 'invoice')),
            ],
            'delivery_template_id' => 'nullable|integer|exists:email_templates,id',
            'payment_confirmation_template_id' => 'nullable|integer|exists:email_templates,id',
            'reminder_bindings' => 'nullable|array',
            'reminder_bindings.*.rule_id' => ['required_with:reminder_bindings', 'string', 'max:64', Rule::in($activeReminderRuleIds)],
            'reminder_bindings.*.template_id' => 'nullable|integer|exists:email_templates,id',
            'reminder_date' => 'nullable|date|after_or_equal:invoice_date',
            'reminder_days_after' => 'nullable|integer|min:0|max:365',
        ]);

        $hasReminderDate = !empty($validatedDates['reminder_date'] ?? null);
        $hasReminderDays = array_key_exists('reminder_days_after', $validatedDates)
            && $validatedDates['reminder_days_after'] !== null
            && $validatedDates['reminder_days_after'] !== '';
        if ($hasReminderDate && $hasReminderDays) {
            return back()->withErrors([
                'reminder_date' => 'Select either a reminder date or days after due date (not both).',
            ])->withInput();
        }
        $invoiceDate = (string) $validatedDates['invoice_date'];
        $dueDate = (string) $validatedDates['due_date'];
        $submittedCurrency = strtoupper((string) ($validatedDates['currency_code'] ?? ''));
        if ($submittedCurrency !== '' && array_key_exists($submittedCurrency, $currencyOptions)) {
            $currencyCode = $submittedCurrency;
        }

        if ($priceMode === InvoiceValidationContract::PRICE_MODE_AUTOMATIC) {
            $validatedSelection = $request->validate([
                'selected_rows' => 'nullable|array',
                'selected_rows.*' => 'integer|min:1',
                'added_rows' => 'nullable|array',
                'added_rows.*.selected' => 'nullable|in:1',
                'added_rows.*.cells' => 'nullable|array',
                'added_rows.*.amount' => 'nullable|string',
            ], [
                'selected_rows.required' => InvoiceValidationContract::MSG_SELECT_AT_LEAST_ONE_ROW,
                'selected_rows.array' => InvoiceValidationContract::MSG_SELECT_AT_LEAST_ONE_ROW,
            ]);

            if ($priceColumn === '' || !in_array($priceColumn, $headers, true)) {
                $this->logFlowValidationFailure('invoice_generate_save', 'invalid_price_column', ['price_column' => $priceColumn]);
                return redirect()->route('invoices.price-select')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_RESELECT_PRICING_COLUMN);
            }
            $selectedRows = array_values(array_unique(array_map('intval', (array) ($validatedSelection['selected_rows'] ?? []))));
            $addedRows = (array) ($validatedSelection['added_rows'] ?? []);
            $hasSelectedAddedRow = collect($addedRows)->contains(function ($row) {
                return (string) (($row['selected'] ?? '')) === '1';
            });
            if (empty($selectedRows) && !$hasSelectedAddedRow) {
                return back()->withErrors(['selected_rows' => InvoiceValidationContract::MSG_SELECT_AT_LEAST_ONE_ROW])->withInput();
            }

            $selectedItemsMap = $this->filterItemMapBySelectedRows($items, $selectedRows);
            $editedRows = (array) $request->input('edited_rows', []);
            [$lineItemsInput, $rowValidationErrors] = $this->buildAutomaticLineItemsFromEditedRows(
                $selectedRows,
                $selectedItemsMap,
                $editedRows,
                $activeIncludeCols,
                $priceColumn
            );
            [$addedLineItems, $addedValidationErrors] = $this->buildAutomaticLineItemsFromAddedRows(
                $addedRows,
                $activeIncludeCols,
                $priceColumn
            );
            $lineItemsInput = array_merge($lineItemsInput, $addedLineItems);
            $rowValidationErrors = array_merge($rowValidationErrors, $addedValidationErrors);

            if (!empty($rowValidationErrors)) {
                $this->logFlowValidationFailure('invoice_generate_save', 'edited_rows_invalid', ['count' => count($rowValidationErrors)]);
                return back()->withErrors(['edited_rows' => implode(' ', $rowValidationErrors)])->withInput();
            }

            if (empty($lineItemsInput)) {
                $this->logFlowValidationFailure('invoice_generate_save', 'no_valid_priced_rows', ['price_column' => $priceColumn]);
                return redirect()->route('invoices.price-select')->with(InvoiceValidationContract::FLASH_ERROR, InvoiceValidationContract::MSG_NO_VALID_PRICED_ROWS);
            }
        } else {
            $validated = $request->validate([
                'manual_total' => 'required|numeric|min:0.01',
            ]);
            $lineItemsInput = [[
                'description' => 'Imported invoice (manual pricing)',
                'quantity' => 1,
                'rate' => (float) $validated['manual_total'],
                'tax_id' => null,
            ]];
        }

        $titleBg = strtoupper(trim((string) $request->input('invoice_title_bg', '#FFDC00')));
        $allowedTitleColors = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];
        if (!in_array($titleBg, $allowedTitleColors, true)) {
            $titleBg = '#FFDC00';
        }
        $titleText = $titleBg === '#FFDC00' ? '#0033D9' : '#FFFFFF';

        $canEditInvoice = has_permission('edit_invoice');
        $taxableInvoice = $canEditInvoice && $request->boolean('taxable_invoice');
        $lineTaxIds = $taxableInvoice
            ? TaxService::sanitizeLineTaxIds((array) $request->input('line_tax_ids', []))
            : [];
        $lineTaxMatrixMode = $taxableInvoice && $request->boolean('line_tax_matrix_mode');
        $lineTaxMatrix = $taxableInvoice
            ? $this->sanitizeLineTaxMatrix((string) $request->input('line_tax_matrix', ''), $lineTaxIds)
            : [];
        $invoiceTaxIds = $taxableInvoice
            ? TaxService::sanitizeInvoiceTaxIds((array) $request->input('invoice_tax_ids', []))
            : [];
        $showBankDetails = $request->boolean('show_bank_details');
        $isRecurring = $request->boolean('is_recurring') && has_permission('manage_recurring_invoices');
        $billToWithBank = (array) $data['bill_to'];
        $billToWithBank = array_merge($billToWithBank, [
            'Bank Account Holder' => trim((string) ($validatedDates['bank_account_holder'] ?? '')),
            'Bank Name' => trim((string) ($validatedDates['bank_name'] ?? '')),
            'Bank Account Number' => trim((string) ($validatedDates['bank_account_number'] ?? '')),
            'Bank IBAN' => trim((string) ($validatedDates['bank_iban'] ?? '')),
            'Bank SWIFT' => trim((string) ($validatedDates['bank_swift'] ?? '')),
            'Bank Routing Code' => trim((string) ($validatedDates['bank_routing_code'] ?? '')),
            'Payment Instructions' => trim((string) ($validatedDates['bank_payment_instructions'] ?? '')),
        ]);
        $invoice = $this->createInvoiceFromImportedLineItems(
            $billToWithBank,
            $lineItemsInput,
            $titleBg,
            $titleText,
            $invoiceDate,
            $dueDate,
            $currencyCode,
            $validatedDates['reminder_date'] ?? null,
            $hasReminderDays ? (int) $validatedDates['reminder_days_after'] : null,
            $invoiceTaxIds,
            $lineTaxIds,
            $lineTaxMatrix,
            $lineTaxMatrixMode,
            $taxableInvoice,
            $showBankDetails,
            $isRecurring,
            $validatedDates['delivery_template_id'] ?? null,
            $validatedDates['payment_confirmation_template_id'] ?? null,
            $this->normalizeReminderBindings((array) ($validatedDates['reminder_bindings'] ?? []))
        );

        $request->session()->forget(InvoiceValidationContract::SESSION_IMPORT);
        $request->session()->forget(InvoiceValidationContract::SESSION_PRICE_CONFIG);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    /**
     * Legacy manual pricing endpoint kept for compatibility; now routes through generate page.
     */
    public function showManualPricing(Request $request)
    {
        return $this->showGenerateFromImport($request);
    }

    /**
     * Legacy manual pricing endpoint kept for compatibility; now routes through generate page.
     */
    public function saveManualPricing(Request $request)
    {
        return $this->saveGenerateFromImport($request);
    }

    private function buildAutomaticLineItemsInput(array $items, array $includeCols, string $priceColumn): array
    {
        $lineItemsInput = [];
        $indexes = $this->detectImportColumnIndexes($includeCols);
        $quantityIndex = $indexes['quantity'];
        $unitPriceIndex = $indexes['unit_price'];
        $totalIndex = $indexes['total'];
        $priceRole = $this->classifyColumnRole($priceColumn);

        foreach ($items as $index => $row) {
            $quantity = 1.0;
            if ($quantityIndex !== null) {
                $qtyCol = $includeCols[$quantityIndex];
                $parsedQty = $this->parseFlexibleNumber((string) ($row[$qtyCol] ?? ''));
                if ($parsedQty !== null) {
                    if ($parsedQty <= 0) {
                        continue;
                    }
                    $quantity = $parsedQty;
                }
            }

            $totalValue = null;
            if ($totalIndex !== null) {
                $totalCol = $includeCols[$totalIndex];
                $parsedTotal = $this->parseFlexibleNumber((string) ($row[$totalCol] ?? ''));
                if ($parsedTotal !== null && $parsedTotal > 0) {
                    $totalValue = $parsedTotal;
                }
            }

            $unitValue = null;
            if ($unitPriceIndex !== null) {
                $unitCol = $includeCols[$unitPriceIndex];
                $parsedUnit = $this->parseFlexibleNumber((string) ($row[$unitCol] ?? ''));
                if ($parsedUnit !== null && $parsedUnit >= 0) {
                    $unitValue = $parsedUnit;
                }
            }

            $rate = null;
            if ($totalValue !== null) {
                $rate = $quantity > 0 ? ($totalValue / $quantity) : $totalValue;
            } elseif ($unitValue !== null) {
                $rate = $unitValue;
            } else {
                $parsedPrice = $this->parseFlexibleNumber((string) ($row[$priceColumn] ?? ''));
                if ($parsedPrice !== null && $parsedPrice > 0) {
                    $rate = $priceRole === 'total' && $quantity > 0 ? ($parsedPrice / $quantity) : $parsedPrice;
                }
            }

            if ($rate === null || $rate <= 0) {
                continue;
            }

            $parts = [];
            foreach ($includeCols as $col) {
                $value = trim((string) ($row[$col] ?? ''));
                if ($value === '') {
                    continue;
                }
                $parts[] = $col . ': ' . $value;
            }

            $description = !empty($parts) ? implode(' | ', $parts) : ('Imported Item #' . ($index + 1));
            $lineItemsInput[] = [
                'description' => $description,
                'quantity' => round($quantity, 2),
                'rate' => round((float) $rate, 2),
                'tax_id' => null,
                'meta_all_fields' => $this->buildLineAllFields($includeCols, $row),
            ];
        }

        return $lineItemsInput;
    }

    private function buildAutomaticPreviewItems(array $items, array $includeCols, string $priceColumn): array
    {
        $lineItems = $this->buildAutomaticLineItemsInput($items, $includeCols, $priceColumn);
        return array_map(fn (array $row) => [
            'description' => $row['description'],
            'rate' => (float) $row['rate'],
        ], $lineItems);
    }

    private function buildPreviewTableRows(array $items, array $includeCols, string $priceColumn, string $priceMode): array
    {
        $rows = [];
        $indexes = $this->detectImportColumnIndexes($includeCols);
        $quantityIndex = $indexes['quantity'];
        $unitPriceIndex = $indexes['unit_price'];
        $totalIndex = $indexes['total'];
        $priceRole = $this->classifyColumnRole($priceColumn);

        foreach ($items as $index => $row) {
            $cells = [];
            foreach ($includeCols as $col) {
                $cells[$col] = (string) ($row[$col] ?? '');
            }

            $amount = null;
            if ($priceMode === 'automatic' && $priceColumn !== '') {
                $quantity = null;
                if ($quantityIndex !== null) {
                    $qtyCol = $includeCols[$quantityIndex];
                    $parsedQty = $this->parseFlexibleNumber((string) ($row[$qtyCol] ?? ''));
                    if ($parsedQty !== null && $parsedQty > 0) {
                        $quantity = $parsedQty;
                    }
                }

                $totalValue = null;
                if ($totalIndex !== null) {
                    $totalCol = $includeCols[$totalIndex];
                    $parsedTotal = $this->parseFlexibleNumber((string) ($row[$totalCol] ?? ''));
                    if ($parsedTotal !== null && $parsedTotal > 0) {
                        $totalValue = $parsedTotal;
                    }
                }

                if ($totalValue !== null) {
                    $amount = $totalValue;
                } else {
                    $unitValue = null;
                    if ($unitPriceIndex !== null) {
                        $unitCol = $includeCols[$unitPriceIndex];
                        $parsedUnit = $this->parseFlexibleNumber((string) ($row[$unitCol] ?? ''));
                        if ($parsedUnit !== null && $parsedUnit >= 0) {
                            $unitValue = $parsedUnit;
                        }
                    }

                    if ($unitValue !== null && $quantity !== null) {
                        $amount = $quantity * $unitValue;
                    } else {
                        $parsedPrice = $this->parseFlexibleNumber((string) ($row[$priceColumn] ?? ''));
                        if ($parsedPrice !== null && $parsedPrice > 0) {
                            if ($priceRole === 'total') {
                                $amount = $parsedPrice;
                            } elseif ($quantity !== null) {
                                $amount = $quantity * $parsedPrice;
                            } else {
                                $amount = $parsedPrice;
                            }
                        }
                    }
                }

                if ($amount === null || $amount <= 0) {
                    continue;
                }
            }

            $rows[] = [
                'index' => $index + 1,
                'cells' => $cells,
                'amount' => $amount,
            ];
        }

        return $rows;
    }

    private function buildManualLineItemsInput(array $items, array $includeCols): array
    {
        $lineItemsInput = [];
        foreach ($items as $index => $row) {
            $parts = [];
            foreach ($includeCols as $col) {
                $value = trim((string) ($row[$col] ?? ''));
                if ($value === '') {
                    continue;
                }
                $parts[] = $col . ': ' . $value;
            }

            $description = !empty($parts) ? implode(' | ', $parts) : ('Imported Item #' . ($index + 1));
            $lineItemsInput[] = [
                'description' => $description,
                'meta_all_fields' => $this->buildLineAllFields($includeCols, $row),
            ];
        }

        return $lineItemsInput;
    }

    private function buildManualPreviewItems(array $items, array $includeCols): array
    {
        $previewItems = [];
        foreach ($items as $index => $row) {
            $parts = [];
            foreach ($includeCols as $col) {
                $value = trim((string) ($row[$col] ?? ''));
                if ($value === '') {
                    continue;
                }
                $parts[] = $col . ': ' . $value;
            }
            $description = !empty($parts) ? implode(' | ', $parts) : ('Imported Item #' . ($index + 1));
            $previewItems[] = ['description' => $description];
        }

        return $previewItems;
    }

    /**
     * Create invoice from import flow line items.
     */
    private function createInvoiceFromImportedLineItems(
        array $billTo,
        array $lineItemsInput,
        string $titleBg = '#FFDC00',
        string $titleText = '#0033D9',
        ?string $invoiceDateInput = null,
        ?string $dueDateInput = null,
        ?string $currencyCodeInput = null,
        ?string $reminderDateInput = null,
        ?int $reminderDaysAfter = null,
        array $invoiceTaxIds = [],
        array $lineTaxIds = [],
        array $lineTaxMatrix = [],
        bool $lineTaxMatrixMode = false,
        bool $taxableInvoice = false,
        bool $showBankDetails = true,
        bool $isRecurring = false,
        ?int $deliveryTemplateId = null,
        ?int $paymentConfirmationTemplateId = null,
        array $reminderBindings = []
    ): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber((string) $billTo['Company Name']);
        $invoiceDate = $invoiceDateInput ? Carbon::parse($invoiceDateInput) : now();
        $dueDate = $dueDateInput ? Carbon::parse($dueDateInput) : $invoiceDate->copy()->addDays(14);
        $recurrenceType = $isRecurring ? 'monthly' : null;
        $nextRunDate = $isRecurring ? $invoiceDate->copy()->addMonth()->toDateString() : null;

        $client = Client::firstOrCreate(
            ['company_name' => $billTo['Company Name']],
            [
                'representative' => $billTo['Contact Name'] ?: null,
                'email' => $billTo['Email'] ?: null,
                'phone' => $billTo['Phone'] ?: null,
                'address' => $billTo['Address'] ?: null,
                'created_by' => Auth::id(),
            ]
        );

        [$lineItems, $subtotal, $lineTaxTotalFromItems, $lineTaxLinesFromItems] = $this->normalizeLineItems($lineItemsInput);
        [$selectedLineTaxLines, $selectedLineTaxTotal] = $this->calculateLineTaxes($lineTaxIds, $lineItemsInput, $lineTaxMatrix, $lineTaxMatrixMode);
        $lineTaxLines = $this->mergeTaxLines($lineTaxLinesFromItems, $selectedLineTaxLines);
        $lineTaxTotal = round($lineTaxTotalFromItems + $selectedLineTaxTotal, 2);
        [$invoiceTaxLines, $invoiceTaxTotal] = $this->calculateInvoiceTaxes($invoiceTaxIds, $subtotal + $lineTaxTotal);
        $totalAmount = round($subtotal + $lineTaxTotal + $invoiceTaxTotal, 2);
        $taxSummary = $this->buildTaxSummary($taxableInvoice, $subtotal, $lineTaxLines, $invoiceTaxLines, $invoiceTaxTotal);

        $currencyCode = strtoupper((string) ($currencyCodeInput ?: SettingService::getSetting('currency_code', 'USD')));
        $currencyOptions = $this->getGenerateCurrencyOptions();
        if (!array_key_exists($currencyCode, $currencyOptions)) {
            $currencyCode = 'USD';
        }
        $currencyDisplay = $currencyOptions[$currencyCode]['display'];
        $invoiceHtml = $this->renderInvoiceHtml($this->buildInvoiceRenderPayload(
            $invoiceNumber,
            $billTo,
            $lineItems,
            $subtotal,
            $lineTaxTotal,
            $invoiceTaxLines,
            $taxSummary,
            $totalAmount,
            $currencyCode,
            $currencyDisplay,
            $invoiceDate->format('Y-m-d'),
            $dueDate->format('Y-m-d'),
            $titleBg,
            $titleText,
            $showBankDetails,
            '',
            'Unpaid'
        ));

        $invoicePayload = [
            'invoice_number' => $invoiceNumber,
            'client_id' => $client->id,
            'bill_to_name' => $billTo['Company Name'],
            'bill_to_json' => $billTo,
            'total_amount' => $totalAmount,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'status' => 'Unpaid',
            'html' => $invoiceHtml,
            'created_by' => Auth::id(),
            'show_bank_details' => $showBankDetails,
            'is_recurring' => $isRecurring,
            'recurrence_type' => $recurrenceType,
            'next_run_date' => $nextRunDate,
            'currency_code' => $currencyCode,
            'currency_display' => $currencyDisplay,
            'invoice_title_bg' => $titleBg,
            'invoice_title_text' => $titleText,
        ];
        if ($this->invoiceTaxSummaryColumnExists()) {
            $invoicePayload['invoice_tax_summary'] = $taxSummary;
        }
        $invoice = Invoice::create($invoicePayload);

        $this->persistInvoiceEmailConfiguration(
            $invoice,
            $deliveryTemplateId,
            $paymentConfirmationTemplateId,
            $reminderBindings
        );
        $this->persistCustomReminderSchedule(
            $invoice,
            $reminderDateInput,
            $reminderDaysAfter
        );

        $this->createPaymentLink($invoice);
        $invoice->refresh();
        $this->renderAndPersistInvoiceDocument($invoice, $this->buildInvoiceRenderPayload(
            $invoiceNumber,
            $billTo,
            $lineItems,
            $subtotal,
            $lineTaxTotal,
            $invoiceTaxLines,
            $taxSummary,
            $totalAmount,
            $currencyCode,
            $currencyDisplay,
            $invoiceDate->format('Y-m-d'),
            $dueDate->format('Y-m-d'),
            $titleBg,
            $titleText,
            $showBankDetails,
            (string) ($invoice->payment_link ?? ''),
            (string) ($invoice->status ?? 'Unpaid')
        ));
        $this->generatePdf($invoice);

        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
        EmailService::sendInvoiceEmail($invoice, $pdfPath);

        return $invoice;
    }

    /**
     * Persist per-invoice email templates and reminder rule-template bindings.
     */
    private function persistInvoiceEmailConfiguration(
        Invoice $invoice,
        ?int $deliveryTemplateId,
        ?int $paymentConfirmationTemplateId,
        array $reminderBindings
    ): void {
        InvoiceEmailConfiguration::query()->updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'delivery_template_id' => $deliveryTemplateId ?: null,
                'payment_confirmation_template_id' => $paymentConfirmationTemplateId ?: null,
            ]
        );

        InvoiceReminderTemplateBinding::query()
            ->where('invoice_id', $invoice->id)
            ->delete();

        if (empty($reminderBindings)) {
            return;
        }

        $rows = [];
        foreach ($reminderBindings as $binding) {
            $rows[] = [
                'invoice_id' => $invoice->id,
                'rule_id' => $binding['rule_id'],
                'template_id' => $binding['template_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        InvoiceReminderTemplateBinding::query()->insert($rows);
    }

    /**
     * Persist an optional custom reminder schedule for an invoice.
     */
    private function persistCustomReminderSchedule(
        Invoice $invoice,
        ?string $reminderDateInput,
        ?int $daysAfterDue
    ): void {
        $reminderDateInput = $reminderDateInput ? trim($reminderDateInput) : null;

        if (!$reminderDateInput && $daysAfterDue === null) {
            return;
        }

        if ($reminderDateInput) {
            $reminderDate = Carbon::parse($reminderDateInput)->startOfDay();
            $offsetDays = null;
            $offsetBase = null;
        } else {
            $base = $invoice->due_date ?: $invoice->invoice_date;
            if (!$base) {
                return;
            }
            $reminderDate = Carbon::parse($base)->startOfDay()->addDays((int) $daysAfterDue);
            $offsetDays = (int) $daysAfterDue;
            $offsetBase = 'due_date';
        }

        InvoiceCustomReminder::query()->updateOrCreate(
            [
                'invoice_id' => $invoice->id,
                'reminder_date' => $reminderDate->toDateString(),
                'template_id' => null,
            ],
            [
                'offset_days' => $offsetDays,
                'offset_base' => $offsetBase,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Ensure reminder bindings only contain active rule IDs and valid template IDs.
     */
    private function normalizeReminderBindings(array $bindings): array
    {
        $activeRuleIds = collect(ReminderRuleService::getRules())
            ->filter(fn (array $rule) => !empty($rule['enabled']) && !empty($rule['id']))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $activeMap = array_fill_keys($activeRuleIds, true);
        $templateMap = ReminderRuleService::getTemplateMap();

        $normalized = [];
        foreach ($bindings as $binding) {
            if (!is_array($binding)) {
                continue;
            }

            $ruleId = trim((string) ($binding['rule_id'] ?? ''));
            $templateId = (int) ($binding['template_id'] ?? 0);

            if ($ruleId === '' || $templateId <= 0 || !isset($activeMap[$ruleId])) {
                if ($ruleId === '' || !isset($activeMap[$ruleId])) {
                    continue;
                }

                $mappedTemplateId = isset($templateMap[$ruleId]) ? (int) $templateMap[$ruleId] : null;
                if ($mappedTemplateId !== null && $mappedTemplateId <= 0) {
                    $mappedTemplateId = null;
                }
                $resolvedTemplate = TemplateResolutionService::resolveReminderTemplate($ruleId, $mappedTemplateId);
                if (!$resolvedTemplate) {
                    continue;
                }
                $templateId = (int) $resolvedTemplate->id;
            }

            $normalized[] = [
                'rule_id' => $ruleId,
                'template_id' => $templateId,
            ];
        }

        return collect($normalized)
            ->unique(fn (array $row) => $row['rule_id'] . '|' . $row['template_id'])
            ->values()
            ->all();
    }

    /**
     * Build default reminder bindings using settings + template resolution.
     */
    private function buildDefaultReminderBindings(array $rules): array
    {
        $templateMap = ReminderRuleService::getTemplateMap();
        $bindings = [];

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $ruleId = trim((string) ($rule['id'] ?? ''));
            if ($ruleId === '') {
                continue;
            }

            $mappedTemplateId = isset($templateMap[$ruleId]) ? (int) $templateMap[$ruleId] : null;
            if ($mappedTemplateId !== null && $mappedTemplateId <= 0) {
                $mappedTemplateId = null;
            }

            $resolvedTemplate = TemplateResolutionService::resolveReminderTemplate($ruleId, $mappedTemplateId);
            if (!$resolvedTemplate) {
                continue;
            }

            $bindings[] = [
                'rule_id' => $ruleId,
                'template_id' => (int) $resolvedTemplate->id,
            ];
        }

        return collect($bindings)
            ->unique(fn (array $row) => $row['rule_id'] . '|' . $row['template_id'])
            ->values()
            ->all();
    }

    /**
     * Format reminder rule label with cadence for UI.
     */
    private function formatReminderRuleLabel(array $rule): string
    {
        $name = trim((string) ($rule['name'] ?? ''));
        $direction = trim((string) ($rule['direction'] ?? ''));
        $days = (int) ($rule['days'] ?? 0);

        $cadence = '';
        if ($direction === 'on') {
            $cadence = 'On due date';
        } elseif ($direction === 'before') {
            $cadence = $days === 1 ? '1 day before due' : $days . ' days before due';
        } elseif ($direction === 'after') {
            $cadence = $days === 1 ? '1 day after due' : $days . ' days after due';
        }

        if ($cadence !== '') {
            return $name !== '' ? $name . ' - ' . $cadence : $cadence;
        }

        if ($name !== '') {
            return $name;
        }

        return trim((string) ($rule['id'] ?? ''));
    }

    private function getGenerateCurrencyOptions(): array
    {
        return [
            'USD' => ['label' => 'USD', 'display' => '$'],
            'CAD' => ['label' => 'CAD', 'display' => 'CA$'],
            'AUD' => ['label' => 'AUD', 'display' => 'A$'],
            'GBP' => ['label' => 'GBP', 'display' => '£'],
            'EUR' => ['label' => 'EUR', 'display' => '€'],
            'PKR' => ['label' => 'PKR', 'display' => 'PKR'],
            'SAR' => ['label' => 'SAR', 'display' => 'SAR'],
            'AED' => ['label' => 'AED', 'display' => 'AED'],
        ];
    }

    private function filterItemMapBySelectedRows(array $items, array $selectedRows): array
    {
        if (empty($selectedRows)) {
            return [];
        }

        $selectedMap = array_fill_keys($selectedRows, true);
        $filtered = [];

        foreach (array_values($items) as $index => $row) {
            $oneBasedIndex = $index + 1;
            if (isset($selectedMap[$oneBasedIndex])) {
                $filtered[$oneBasedIndex] = $row;
            }
        }

        return $filtered;
    }

    private function buildAutomaticLineItemsFromEditedRows(
        array $selectedRows,
        array $selectedItemsMap,
        array $editedRows,
        array $includeCols,
        string $priceColumn
    ): array {
        $lineItemsInput = [];
        $errors = [];

        $indexes = $this->detectImportColumnIndexes($includeCols);
        $descriptionIndex = $indexes['description'];
        $quantityIndex = $indexes['quantity'];
        $unitPriceIndex = $indexes['unit_price'];
        $totalIndex = $indexes['total'];
        $priceColumnIndex = array_search($priceColumn, $includeCols, true);
        $priceColumnRole = $this->classifyColumnRole($priceColumn);

        foreach ($selectedRows as $rowId) {
            if (!isset($selectedItemsMap[$rowId])) {
                continue;
            }

            $sourceRow = (array) $selectedItemsMap[$rowId];
            $payload = (array) ($editedRows[$rowId] ?? []);
            $cells = (array) ($payload['cells'] ?? []);

            $assoc = [];
            foreach ($includeCols as $idx => $col) {
                $assoc[$col] = array_key_exists($idx, $cells) ? (string) $cells[$idx] : (string) ($sourceRow[$col] ?? '');
            }

            $quantity = 1.0;
            if ($quantityIndex !== null) {
                $qtyCol = $includeCols[$quantityIndex];
                $parsedQty = $this->parseFlexibleNumber((string) ($assoc[$qtyCol] ?? ''));
                if ($parsedQty !== null) {
                    if ($parsedQty <= 0) {
                        $errors[] = "Row {$rowId}: Quantity must be a number greater than 0.";
                        continue;
                    }
                    $quantity = $parsedQty;
                }
            }

            $totalValue = null;
            $payloadAmountRaw = (string) ($payload['amount'] ?? '');
            if ($payloadAmountRaw !== '') {
                $parsedPayloadAmount = $this->parseFlexibleNumber($payloadAmountRaw);
                if ($parsedPayloadAmount === null || $parsedPayloadAmount < 0) {
                    $errors[] = "Row {$rowId}: Total Amount must be a valid non-negative number.";
                    continue;
                }
                if ($parsedPayloadAmount > 0) {
                    $totalValue = $parsedPayloadAmount;
                }
            }

            if ($totalValue === null && $totalIndex !== null) {
                $totalCol = $includeCols[$totalIndex];
                $totalRaw = (string) ($assoc[$totalCol] ?? '');
                if ($totalRaw !== '') {
                    $parsedTotal = $this->parseFlexibleNumber($totalRaw);
                    if ($parsedTotal === null || $parsedTotal < 0) {
                        $errors[] = "Row {$rowId}: Total Amount must be a valid non-negative number.";
                        continue;
                    }
                    if ($parsedTotal > 0) {
                        $totalValue = $parsedTotal;
                    }
                }
            }

            $unitValue = null;
            if ($unitPriceIndex !== null) {
                $unitCol = $includeCols[$unitPriceIndex];
                $unitRaw = (string) ($assoc[$unitCol] ?? '');
                if ($unitRaw !== '') {
                    $parsedUnit = $this->parseFlexibleNumber($unitRaw);
                    if ($parsedUnit === null || $parsedUnit < 0) {
                        $errors[] = "Row {$rowId}: Unit Price must be a valid non-negative number.";
                        continue;
                    }
                    $unitValue = $parsedUnit;
                }
            }

            $rate = null;
            if ($totalValue !== null) {
                $rate = $quantity > 0 ? ($totalValue / $quantity) : $totalValue;
            } elseif ($unitValue !== null) {
                $rate = $unitValue;
            } else {
                $priceRaw = (string) ($assoc[$priceColumn] ?? '');
                if ($priceRaw !== '') {
                    $parsedPrice = $this->parseFlexibleNumber($priceRaw);
                    if ($parsedPrice === null || $parsedPrice < 0) {
                        $errors[] = "Row {$rowId}: Price column must be a valid non-negative number.";
                        continue;
                    }
                    if ($parsedPrice > 0) {
                        $rate = $priceColumnRole === 'total' && $quantity > 0 ? ($parsedPrice / $quantity) : $parsedPrice;
                    }
                } else {
                    $parsedPrice = $this->parseFlexibleNumber((string) ($sourceRow[$priceColumn] ?? ''));
                    if ($parsedPrice !== null && $parsedPrice > 0) {
                        $rate = $priceColumnRole === 'total' && $quantity > 0 ? ($parsedPrice / $quantity) : $parsedPrice;
                    }
                }
            }

            if ($rate === null || $rate <= 0) {
                continue;
            }

            $description = '';
            if ($descriptionIndex !== null) {
                $descCol = $includeCols[$descriptionIndex];
                $description = trim((string) ($assoc[$descCol] ?? ''));
            }
            if ($description === '') {
                $parts = [];
                foreach ($includeCols as $col) {
                    $value = trim((string) ($assoc[$col] ?? ''));
                    if ($value !== '') {
                        $parts[] = $col . ': ' . $value;
                    }
                }
                $description = !empty($parts) ? implode(' | ', $parts) : ('Imported Item #' . $rowId);
            }

            $lineItemsInput[] = [
                'description' => $description,
                'quantity' => round($quantity, 2),
                'rate' => round((float) $rate, 2),
                'tax_id' => null,
                'source_row_key' => 'row-' . $rowId,
                'meta_fields' => $this->buildLineMetaFields(
                    $includeCols,
                    $assoc,
                    [$descriptionIndex, $quantityIndex, $unitPriceIndex, $totalIndex, $priceColumnIndex]
                ),
                'meta_all_fields' => $this->buildLineAllFields($includeCols, $assoc),
            ];
        }

        return [$lineItemsInput, $errors];
    }

    private function buildAutomaticLineItemsFromAddedRows(
        array $addedRows,
        array $includeCols,
        string $priceColumn
    ): array {
        $lineItemsInput = [];
        $errors = [];

        $indexes = $this->detectImportColumnIndexes($includeCols);
        $descriptionIndex = $indexes['description'];
        $quantityIndex = $indexes['quantity'];
        $unitPriceIndex = $indexes['unit_price'];
        $totalIndex = $indexes['total'];
        $priceColumnIndex = array_search($priceColumn, $includeCols, true);
        $priceColumnRole = $this->classifyColumnRole($priceColumn);

        foreach ($addedRows as $rawRowKey => $rowPayload) {
            if ((string) (($rowPayload['selected'] ?? '')) !== '1') {
                continue;
            }
            $normalizedRowKey = (string) $rawRowKey;
            $displayRowNumber = ctype_digit($normalizedRowKey) ? ((int) $normalizedRowKey + 1) : ($normalizedRowKey ?: '1');

            $cells = (array) ($rowPayload['cells'] ?? []);
            $assoc = [];
            foreach ($includeCols as $idx => $col) {
                $assoc[$col] = array_key_exists($idx, $cells) ? (string) $cells[$idx] : '';
            }

            $rowLabel = 'New row ' . $displayRowNumber;

            $quantity = 1.0;
            if ($quantityIndex !== null) {
                $qtyCol = $includeCols[$quantityIndex];
                $parsedQty = $this->parseFlexibleNumber((string) ($assoc[$qtyCol] ?? ''));
                if ($parsedQty !== null) {
                    if ($parsedQty <= 0) {
                        $errors[] = "{$rowLabel}: Quantity must be a number greater than 0.";
                        continue;
                    }
                    $quantity = $parsedQty;
                }
            }

            $totalValue = null;
            $payloadAmountRaw = (string) ($rowPayload['amount'] ?? '');
            if ($payloadAmountRaw !== '') {
                $parsedPayloadAmount = $this->parseFlexibleNumber($payloadAmountRaw);
                if ($parsedPayloadAmount === null || $parsedPayloadAmount < 0) {
                    $errors[] = "{$rowLabel}: Total Amount must be a valid non-negative number.";
                    continue;
                }
                if ($parsedPayloadAmount > 0) {
                    $totalValue = $parsedPayloadAmount;
                }
            }

            if ($totalValue === null && $totalIndex !== null) {
                $totalCol = $includeCols[$totalIndex];
                $totalRaw = (string) ($assoc[$totalCol] ?? '');
                if ($totalRaw !== '') {
                    $parsedTotal = $this->parseFlexibleNumber($totalRaw);
                    if ($parsedTotal === null || $parsedTotal < 0) {
                        $errors[] = "{$rowLabel}: Total Amount must be a valid non-negative number.";
                        continue;
                    }
                    if ($parsedTotal > 0) {
                        $totalValue = $parsedTotal;
                    }
                }
            }

            $unitValue = null;
            if ($unitPriceIndex !== null) {
                $unitCol = $includeCols[$unitPriceIndex];
                $unitRaw = (string) ($assoc[$unitCol] ?? '');
                if ($unitRaw !== '') {
                    $parsedUnit = $this->parseFlexibleNumber($unitRaw);
                    if ($parsedUnit === null || $parsedUnit < 0) {
                        $errors[] = "{$rowLabel}: Unit Price must be a valid non-negative number.";
                        continue;
                    }
                    $unitValue = $parsedUnit;
                }
            }

            $rate = null;
            if ($totalValue !== null) {
                $rate = $quantity > 0 ? ($totalValue / $quantity) : $totalValue;
            } elseif ($unitValue !== null) {
                $rate = $unitValue;
            } else {
                $priceRaw = $priceColumnIndex !== false ? (string) ($assoc[$includeCols[$priceColumnIndex]] ?? '') : '';
                if ($priceRaw !== '') {
                    $parsedPrice = $this->parseFlexibleNumber($priceRaw);
                    if ($parsedPrice === null || $parsedPrice < 0) {
                        $errors[] = "{$rowLabel}: Price column must be a valid non-negative number.";
                        continue;
                    }
                    if ($parsedPrice > 0) {
                        $rate = $priceColumnRole === 'total' && $quantity > 0 ? ($parsedPrice / $quantity) : $parsedPrice;
                    }
                }
            }

            if ($rate === null || $rate <= 0) {
                continue;
            }

            $description = '';
            if ($descriptionIndex !== null) {
                $descCol = $includeCols[$descriptionIndex];
                $description = trim((string) ($assoc[$descCol] ?? ''));
            }
            if ($description === '') {
                $parts = [];
                foreach ($includeCols as $col) {
                    $value = trim((string) ($assoc[$col] ?? ''));
                    if ($value !== '') {
                        $parts[] = $col . ': ' . $value;
                    }
                }
                $description = !empty($parts) ? implode(' | ', $parts) : "Added Line Item " . $displayRowNumber;
            }

            $lineItemsInput[] = [
                'description' => $description,
                'quantity' => round($quantity, 2),
                'rate' => round((float) $rate, 2),
                'tax_id' => null,
                'source_row_key' => 'new-' . $normalizedRowKey,
                'meta_fields' => $this->buildLineMetaFields(
                    $includeCols,
                    $assoc,
                    [$descriptionIndex, $quantityIndex, $unitPriceIndex, $totalIndex, $priceColumnIndex]
                ),
                'meta_all_fields' => $this->buildLineAllFields($includeCols, $assoc),
            ];
        }

        return [$lineItemsInput, $errors];
    }

    private function findIncludeColIndex(array $includeCols, array $needles): ?int
    {
        foreach ($includeCols as $idx => $col) {
            $label = strtolower(trim((string) $col));
            foreach ($needles as $needle) {
                if (str_contains($label, strtolower($needle))) {
                    return $idx;
                }
            }
        }

        return null;
    }

    private function normalizeColumnLabel(string $label): string
    {
        return strtolower(trim($label));
    }

    private function labelMatches(string $label, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($label, strtolower($needle))) {
                return true;
            }
        }
        return false;
    }

    private function classifyColumnRole(string $label): string
    {
        $label = $this->normalizeColumnLabel($label);

        if ($this->labelMatches($label, ['item description', 'description', 'service', 'details'])) {
            return 'description';
        }
        if ($this->labelMatches($label, ['quantity', 'qty'])) {
            return 'quantity';
        }
        if ($this->labelMatches($label, ['unit price', 'price', 'rate'])) {
            return 'unit_price';
        }
        if ($this->labelMatches($label, ['subtotal', 'sub total', 'line total', 'total amount', 'amount', 'total'])) {
            return 'total';
        }

        return '';
    }

    private function detectImportColumnIndexes(array $includeCols): array
    {
        return [
            'description' => $this->findIncludeColIndex($includeCols, ['item description', 'description', 'service', 'details']),
            'quantity' => $this->findIncludeColIndex($includeCols, ['quantity', 'qty']),
            'unit_price' => $this->findIncludeColIndex($includeCols, ['unit price', 'price', 'rate']),
            'total' => $this->findIncludeColIndex($includeCols, ['subtotal', 'sub total', 'line total', 'total amount', 'amount', 'total']),
        ];
    }

    private function parseFlexibleNumber(?string $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $manual = $this->parseManualNumber($raw);
        if ($manual !== null) {
            return $manual;
        }

        $imported = $this->parseImportedAmount($raw);
        if ($imported == 0.0 && !preg_match('/[0-9]/', $raw)) {
            return null;
        }

        return (float) $imported;
    }

    private function pickRecommendedPriceColumn(array $headers): ?string
    {
        $totalNeedles = ['subtotal', 'sub total', 'line total', 'total amount', 'amount', 'total'];
        $unitNeedles = ['unit price', 'price', 'rate'];

        foreach ($headers as $header) {
            $label = $this->normalizeColumnLabel((string) $header);
            if ($this->labelMatches($label, $totalNeedles)) {
                return (string) $header;
            }
        }

        foreach ($headers as $header) {
            $label = $this->normalizeColumnLabel((string) $header);
            if ($this->labelMatches($label, $unitNeedles)) {
                return (string) $header;
            }
        }

        return $headers[0] ?? null;
    }

    private function pickRecommendedIncludeCols(array $headers, ?string $priceColumn): array
    {
        $desired = [
            'item description',
            'description',
            'service',
            'details',
            'quantity',
            'qty',
            'unit price',
            'price',
            'rate',
            'subtotal',
            'sub total',
            'line total',
            'total amount',
            'amount',
            'total',
        ];

        $include = [];
        foreach ($headers as $header) {
            $label = $this->normalizeColumnLabel((string) $header);
            if ($this->labelMatches($label, $desired)) {
                $include[] = (string) $header;
            }
        }

        if ($priceColumn && !in_array($priceColumn, $include, true)) {
            $include[] = $priceColumn;
        }

        if (empty($include)) {
            $include = array_slice($headers, 0, InvoiceValidationContract::INCLUDE_COLS_MAX);
        }

        if (count($include) > InvoiceValidationContract::INCLUDE_COLS_MAX) {
            $include = array_slice($include, 0, InvoiceValidationContract::INCLUDE_COLS_MAX);
        }

        return $include;
    }

    private function parseManualNumber(?string $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', ''], $raw);
        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function invoiceTaxSummaryColumnExists(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'invoice_tax_summary');
        return $hasColumn;
    }

    private function logFlowValidationFailure(string $flow, string $reason, array $context = []): void
    {
        Log::warning('invoice_validation_failure', array_merge([
            'flow' => $flow,
            'reason' => $reason,
            'user_id' => Auth::id(),
        ], $context));
    }

    private function normalizeLineItems(array $items): array
    {
        $lineItems = [];
        $subtotal = 0.0;
        $lineTaxTotal = 0.0;
        $lineTaxLinesMap = [];

        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $rate = (float) $item['rate'];
            $lineTotal = round($quantity * $rate, 2);
            $subtotal += $lineTotal;

            $taxLabel = 'N/A';
            if (!empty($item['tax_id'])) {
                $tax = Tax::lineLevel()->find($item['tax_id']);
                if ($tax) {
                    $taxAmount = round($lineTotal * ((float) $tax->percentage / 100), 2);
                    $lineTaxTotal += $taxAmount;
                    $taxLabel = "{$tax->name} ({$tax->percentage}%)";
                    $lineTaxLinesMap[$tax->id] = $lineTaxLinesMap[$tax->id] ?? [
                        'id' => $tax->id,
                        'label' => $taxLabel,
                        'amount' => 0.0,
                    ];
                    $lineTaxLinesMap[$tax->id]['amount'] += $taxAmount;
                }
            }

            $metaFields = array_values(array_filter((array) ($item['meta_fields'] ?? []), function ($meta) {
                return is_array($meta)
                    && isset($meta['label'], $meta['value'])
                    && trim((string) $meta['label']) !== ''
                    && trim((string) $meta['value']) !== '';
            }));
            $metaAllFields = array_values(array_filter((array) ($item['meta_all_fields'] ?? []), function ($meta) {
                return is_array($meta)
                    && isset($meta['label'], $meta['value'])
                    && trim((string) $meta['label']) !== ''
                    && trim((string) $meta['value']) !== '';
            }));

            $lineItems[] = [
                'description' => $item['description'],
                'quantity' => $quantity,
                'rate' => $rate,
                'tax_label' => $taxLabel,
                'line_total' => $lineTotal,
                'meta_fields' => $metaFields,
                'meta_all_fields' => $metaAllFields,
            ];
        }

        $lineTaxLines = array_map(function (array $line): array {
            $line['amount'] = round((float) $line['amount'], 2);
            return $line;
        }, array_values($lineTaxLinesMap));

        return [$lineItems, round($subtotal, 2), round($lineTaxTotal, 2), $lineTaxLines];
    }

    private function calculateInvoiceTaxes(array $taxIds, float $baseAmount): array
    {
        if (empty($taxIds)) {
            return [[], 0.0];
        }

        $taxes = TaxService::getInvoiceTaxesForCalculation($taxIds);
        $subtotalStage = [];
        $adjustedStage = [];
        $total = 0.0;

        foreach ($taxes as $tax) {
            if ((int) $tax->calc_order === 3) {
                $adjustedStage[] = $tax;
            } else {
                $subtotalStage[] = $tax;
            }
        }

        $lines = [];
        $subtotalTaxTotal = 0.0;
        foreach ($subtotalStage as $tax) {
            $amount = round($baseAmount * ((float) $tax->percentage / 100), 2);
            $lines[] = [
                'id' => $tax->id,
                'label' => "{$tax->name} ({$tax->percentage}% on Subtotal)",
                'amount' => $amount,
            ];
            $subtotalTaxTotal += $amount;
        }

        $adjustedBase = $baseAmount + $subtotalTaxTotal;
        foreach ($adjustedStage as $tax) {
            $amount = round($adjustedBase * ((float) $tax->percentage / 100), 2);
            $lines[] = [
                'id' => $tax->id,
                'label' => "{$tax->name} ({$tax->percentage}% on Adjusted Subtotal)",
                'amount' => $amount,
            ];
            $total += $amount;
        }

        $total += $subtotalTaxTotal;

        return [$lines, round($total, 2)];
    }

    private function calculateLineTaxes(
        array $lineTaxIds,
        array $lineItemsInput,
        array $lineTaxMatrix = [],
        bool $useLineTaxMatrix = false
    ): array
    {
        if (empty($lineTaxIds) || empty($lineItemsInput)) {
            return [[], 0.0];
        }

        $taxes = TaxService::getLineTaxesForCalculation($lineTaxIds);
        if ($taxes->isEmpty()) {
            return [[], 0.0];
        }

        $taxMap = [];
        foreach ($taxes as $tax) {
            $taxMap[(int) $tax->id] = $tax;
        }

        $lineAmountMap = [];
        $total = 0.0;

        foreach ($lineItemsInput as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $lineTotal = round($quantity * $rate, 2);
            if ($lineTotal <= 0) {
                continue;
            }

            $sourceRowKey = trim((string) ($item['source_row_key'] ?? ''));
            if (!$useLineTaxMatrix) {
                $appliedTaxIds = $lineTaxIds;
            } elseif ($sourceRowKey !== '') {
                $appliedTaxIds = array_map('intval', (array) ($lineTaxMatrix[$sourceRowKey] ?? []));
            } else {
                $appliedTaxIds = $lineTaxIds;
            }

            foreach ($appliedTaxIds as $taxId) {
                if ($taxId <= 0 || !isset($taxMap[$taxId])) {
                    continue;
                }
                $tax = $taxMap[$taxId];
                $taxAmount = round($lineTotal * ((float) $tax->percentage / 100), 2);
                if ($taxAmount <= 0) {
                    continue;
                }
                $lineAmountMap[$taxId] = ($lineAmountMap[$taxId] ?? 0.0) + $taxAmount;
                $total += $taxAmount;
            }
        }

        $lines = [];
        foreach ($taxes as $tax) {
            $taxId = (int) $tax->id;
            $amount = round((float) ($lineAmountMap[$taxId] ?? 0.0), 2);
            if ($amount <= 0) {
                continue;
            }
            $lines[] = [
                'id' => $taxId,
                'label' => "{$tax->name} ({$tax->percentage}%)",
                'amount' => $amount,
            ];
        }

        return [$lines, round($total, 2)];
    }

    private function sanitizeLineTaxMatrix(string $raw, array $allowedTaxIds): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $allowedMap = array_fill_keys(array_map('intval', $allowedTaxIds), true);
        $sanitized = [];

        foreach ($decoded as $rowKey => $taxIds) {
            $normalizedRowKey = trim((string) $rowKey);
            if ($normalizedRowKey === '') {
                continue;
            }

            $ids = array_values(array_unique(array_map('intval', (array) $taxIds)));
            $ids = array_values(array_filter($ids, fn (int $id) => $id > 0 && isset($allowedMap[$id])));
            if (!empty($ids)) {
                $sanitized[$normalizedRowKey] = $ids;
            }
        }

        return $sanitized;
    }

    private function mergeTaxLines(array $existingLines, array $additionalLines): array
    {
        $map = [];
        foreach (array_merge($existingLines, $additionalLines) as $line) {
            $id = (int) ($line['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            if (!isset($map[$id])) {
                $map[$id] = [
                    'id' => $id,
                    'label' => (string) ($line['label'] ?? ''),
                    'amount' => 0.0,
                ];
            }
            $map[$id]['amount'] += (float) ($line['amount'] ?? 0);
        }

        return array_map(function (array $line): array {
            $line['amount'] = round((float) $line['amount'], 2);
            return $line;
        }, array_values($map));
    }

    private function buildTaxSummary(
        bool $taxableOn,
        float $netTotal,
        array $lineTaxLines,
        array $invoiceTaxLines,
        float $invoiceTaxTotal
    ): array {
        $lineTaxTotal = round(array_sum(array_map(fn (array $line) => (float) ($line['amount'] ?? 0), $lineTaxLines)), 2);
        $subtotal = round($netTotal + $lineTaxTotal, 2);
        $grandTotal = round($subtotal + $invoiceTaxTotal, 2);
        $invoiceSubtotalTaxes = array_values(array_filter($invoiceTaxLines, fn (array $line) => str_contains((string) ($line['label'] ?? ''), 'on Subtotal)')));
        $invoiceAdjustedTaxes = array_values(array_filter($invoiceTaxLines, fn (array $line) => str_contains((string) ($line['label'] ?? ''), 'on Adjusted Subtotal)')));
        $invoiceSubtotalTaxTotal = round(array_sum(array_map(fn (array $line) => (float) ($line['amount'] ?? 0), $invoiceSubtotalTaxes)), 2);
        $adjustedSubtotal = round($subtotal + $invoiceSubtotalTaxTotal, 2);

        return [
            'taxable_on' => $taxableOn,
            'net_total' => round($netTotal, 2),
            'line_taxes' => $lineTaxLines,
            'line_tax_total' => $lineTaxTotal,
            'subtotal' => $subtotal,
            'invoice_subtotal_taxes' => $invoiceSubtotalTaxes,
            'invoice_adjusted_taxes' => $invoiceAdjustedTaxes,
            'invoice_tax_total' => round($invoiceTaxTotal, 2),
            'invoice_taxes' => $invoiceTaxLines,
            'invoice_subtotal_tax_total' => $invoiceSubtotalTaxTotal,
            'adjusted_subtotal' => $adjustedSubtotal,
            'total_taxes' => round($lineTaxTotal + $invoiceTaxTotal, 2),
            'grand_total' => $grandTotal,
        ];
    }

    private function renderInvoiceHtml(array $data): string
    {
        return view('invoices.template', $data)->render();
    }

    private function getCompanyInfo(): array
    {
        return [
            'name' => SettingService::getSetting('company_name', 'DocuBills'),
            'email' => SettingService::getSetting('company_email', ''),
            'phone' => SettingService::getSetting('company_phone', ''),
            'address' => SettingService::getSetting('company_address', ''),
            'logo' => $this->normalizeCompanyLogo((string) SettingService::getSetting('company_logo', 'homepage/images/docubills-logo.png')),
            'gst_hst' => SettingService::getSetting('gst_number', ''),
            'invoice_footer' => SettingService::getSetting('invoice_footer', ''),
        ];
    }

    private function buildInvoiceRenderPayload(
        string $invoiceNumber,
        array $billTo,
        array $lineItems,
        float $subtotal,
        float $lineTaxTotal,
        array $invoiceTaxLines,
        array $taxSummary,
        float $totalAmount,
        string $currencyCode,
        string $currencyDisplay,
        string $invoiceDate,
        string $dueDate,
        string $titleBg,
        string $titleText,
        bool $showBankDetails,
        string $paymentLink = '',
        string $invoiceStatus = 'Unpaid'
    ): array {
        return [
            'invoiceNumber' => $invoiceNumber,
            'billTo' => $billTo,
            'lineItems' => $lineItems,
            'subtotal' => $subtotal,
            'lineTaxTotal' => $lineTaxTotal,
            'invoiceTaxLines' => $invoiceTaxLines,
            'taxSummary' => $taxSummary,
            'total' => $totalAmount,
            'currencyDisplay' => $currencyDisplay,
            'invoiceDate' => $invoiceDate,
            'dueDate' => $dueDate,
            'company' => $this->getCompanyInfo(),
            'titleBg' => $titleBg,
            'titleText' => $titleText,
            'showBankDetails' => $showBankDetails,
            'paymentLink' => $paymentLink,
            'invoiceStatus' => $invoiceStatus,
            'documentMeta' => [
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'currency_code' => $currencyCode,
                'currency_display' => $currencyDisplay,
                'title_bg' => $titleBg,
                'title_text' => $titleText,
                'show_bank_details' => $showBankDetails,
            ],
        ];
    }

    private function renderAndPersistInvoiceDocument(Invoice $invoice, array $payload): void
    {
        $invoice->html = $this->renderInvoiceHtml($payload);
        $invoice->save();
        $invoice->refresh();
    }

    private function refreshInvoiceDocument(Invoice $invoice): void
    {
        $status = (string) ($invoice->status ?? '');
        $paymentLink = (string) ($invoice->payment_link ?? '');
        $invoice->html = $this->syncPayNowBlockWithInvoiceState((string) ($invoice->html ?? ''), $status, $paymentLink);
        $invoice->save();
        $invoice->refresh();
        $this->generatePdf($invoice);
    }

    private function syncPayNowBlockWithInvoiceState(string $html, string $status, string $paymentLink): string
    {
        $cleanHtml = preg_replace('/<!-- PAY_NOW_BLOCK_START -->.*?<!-- PAY_NOW_BLOCK_END -->/is', '', $html) ?? $html;
        $isUnpaid = strtolower(trim($status)) === 'unpaid';
        $paymentLink = trim($paymentLink);
        if (!$isUnpaid) {
            return $cleanHtml;
        }

        $isDisabled = $paymentLink === '';
        $href = $isDisabled ? '#' : $paymentLink;
        $disabledClass = $isDisabled ? ' pay-now-button-disabled' : '';
        $payNowBlock = "\n    <!-- PAY_NOW_BLOCK_START -->\n    <div class=\"pay-now-row\">\n      <a href=\"" . e($href) . "\" class=\"pay-now-button{$disabledClass}\" target=\"_blank\">Pay Now</a>\n    </div>\n    <!-- PAY_NOW_BLOCK_END -->\n";
        $footerMarker = '<div class="invoice-footer">';
        $position = strpos($cleanHtml, $footerMarker);
        if ($position === false) {
            return $cleanHtml . $payNowBlock;
        }

        return substr($cleanHtml, 0, $position) . $payNowBlock . substr($cleanHtml, $position);
    }

    private function buildLineMetaFields(array $includeCols, array $assoc, array $excludedIndexes = []): array
    {
        $skipIndexes = array_fill_keys(array_filter(array_map(function ($idx) {
            return is_int($idx) ? $idx : null;
        }, $excludedIndexes), fn ($idx) => $idx !== null), true);
        $meta = [];

        foreach ($includeCols as $idx => $col) {
            if (isset($skipIndexes[$idx])) {
                continue;
            }
            $label = trim((string) $col);
            $value = trim((string) ($assoc[$col] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }
            $meta[] = ['label' => $label, 'value' => $value];
        }

        return $meta;
    }

    private function buildLineAllFields(array $includeCols, array $assoc): array
    {
        $fields = [];
        foreach ($includeCols as $col) {
            $label = trim((string) $col);
            $value = trim((string) ($assoc[$col] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }
            $fields[] = ['label' => $label, 'value' => $value];
        }

        return $fields;
    }

    private function normalizeCompanyLogo(string $logo): string
    {
        $logo = trim($logo);
        if ($logo === '') {
            return '';
        }

        if (preg_match('/^(https?:)?\\/\\//i', $logo) || str_starts_with($logo, 'data:image/')) {
            return $logo;
        }

        if (preg_match('/^[a-zA-Z]:\\\\/', $logo) || str_starts_with($logo, '/')) {
            $absolutePath = $logo;
            if (is_file($absolutePath)) {
                return 'file:///' . str_replace('\\', '/', ltrim($absolutePath, '\\/'));
            }
        }

        if (is_file(public_path(ltrim($logo, '/')))) {
            return url('/' . ltrim($logo, '/'));
        }

        return url('/' . ltrim($logo, '/'));
    }

    private function getInvoiceBankingDefaults(): array
    {
        $defaults = [
            'bank_account_holder' => '',
            'bank_name' => '',
            'bank_account_number' => '',
            'bank_iban' => '',
            'bank_swift' => '',
            'bank_routing_code' => '',
            'bank_payment_instructions' => '',
        ];

        $settingsMap = [
            'bank_account_holder' => [
                'bank_account_holder',
                'bank_account_name',
                'account_holder_name',
                'account_holder',
                'bank_holder_name',
                'beneficiary_name',
                'account_name',
                'payment_methods.bank_account_holder',
                'payment_methods.account_holder_name',
                'payment_methods.account_holder',
                'payment_methods.bank_holder_name',
                'payment_methods.beneficiary_name',
                'payment_methods.account_name',
                'payment_methods.bank.account_holder_name',
                'payment_methods.bank.account_holder',
                'payment_method_details.bank_account_holder',
                'payment_method_details.account_holder_name',
                'bank_details.bank_account_holder',
                'bank_details.account_holder_name',
            ],
            'bank_name' => [
                'bank_name',
                'payment_methods.bank_name',
                'payment_methods.bank.bank_name',
                'payment_method_details.bank_name',
                'bank_details.bank_name',
            ],
            'bank_account_number' => [
                'bank_account_number',
                'account_number',
                'payment_methods.bank_account_number',
                'payment_methods.account_number',
                'payment_methods.bank.account_number',
                'payment_method_details.bank_account_number',
                'payment_method_details.account_number',
                'bank_details.bank_account_number',
                'bank_details.account_number',
            ],
            'bank_iban' => [
                'bank_iban',
                'iban',
                'payment_methods.bank_iban',
                'payment_methods.iban',
                'payment_methods.bank.iban',
                'payment_method_details.bank_iban',
                'payment_method_details.iban',
                'bank_details.bank_iban',
                'bank_details.iban',
            ],
            'bank_swift' => [
                'bank_swift',
                'swift',
                'swift_bic',
                'payment_methods.bank_swift',
                'payment_methods.swift',
                'payment_methods.swift_bic',
                'payment_methods.bank.swift',
                'payment_method_details.bank_swift',
                'payment_method_details.swift',
                'bank_details.bank_swift',
                'bank_details.swift',
            ],
            'bank_routing_code' => [
                'bank_routing_code',
                'bank_routing',
                'routing_code',
                'sort_code',
                'payment_methods.bank_routing_code',
                'payment_methods.routing_code',
                'payment_methods.sort_code',
                'payment_methods.bank.routing_code',
                'payment_method_details.bank_routing_code',
                'payment_method_details.routing_code',
                'bank_details.bank_routing_code',
                'bank_details.routing_code',
            ],
            'bank_payment_instructions' => [
                'bank_payment_instructions',
                'bank_additional_info',
                'payment_instructions',
                'payment_methods.bank_payment_instructions',
                'payment_methods.payment_instructions',
                'payment_methods.instructions',
                'payment_method_details.bank_payment_instructions',
                'payment_method_details.payment_instructions',
                'bank_details.bank_payment_instructions',
                'bank_details.payment_instructions',
            ],
        ];

        foreach ($settingsMap as $targetKey => $possibleKeys) {
            foreach ($possibleKeys as $sourceKey) {
                $value = $this->resolveSettingValue($sourceKey);
                if ($value !== '') {
                    $defaults[$targetKey] = $value;
                    break;
                }
            }
        }

        return $defaults;
    }

    private function resolveSettingValue(string $key): string
    {
        if (!str_contains($key, '.')) {
            return trim((string) SettingService::getSetting($key, ''));
        }

        [$rootKey, $path] = explode('.', $key, 2);
        $raw = trim((string) SettingService::getSetting($rootKey, ''));
        if ($raw === '') {
            return '';
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return '';
        }

        $node = $decoded;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return '';
            }
            $node = $node[$segment];
        }

        return is_scalar($node) ? trim((string) $node) : '';
    }

    private function mapImportColumns(array $header): array
    {
        $find = function (array $names) use ($header) {
            foreach ($header as $key => $value) {
                if (in_array($value, $names, true)) {
                    return $key;
                }
            }
            return null;
        };

        return [
            'company' => $find(['company', 'company name', 'client', 'client name']) ?? 'A',
            'contact' => $find(['contact', 'contact name']) ?? 'B',
            'email' => $find(['email', 'email address']) ?? 'C',
            'phone' => $find(['phone', 'phone number']) ?? 'D',
            'address' => $find(['address']) ?? 'E',
            'amount' => $find(['amount', 'total', 'total amount']) ?? 'F',
            'invoice_date' => $find(['invoice date', 'date']) ?? 'G',
            'due_date' => $find(['due date', 'due']) ?? 'H',
        ];
    }

    /**
     * Load spreadsheet rows in the same shape as PhpSpreadsheet's toArray(..., true, true, true).
     */
    private function loadSpreadsheetRowsFromUploadedFile($file): ?array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?? '');
        if ($ext === 'xlsx' && !class_exists('ZipArchive')) {
            return null;
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        return count($rows) >= 2 ? $rows : null;
    }

    /**
     * Convert Google Sheet URL to CSV export URL.
     */
    private function toGoogleCsvExportUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = (string) ($parts['path'] ?? '');

        if ($host !== 'docs.google.com' || !preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $path, $matches)) {
            return null;
        }

        $sheetId = $matches[1];
        parse_str($parts['query'] ?? '', $query);
        $gid = isset($query['gid']) ? '&gid=' . urlencode((string) $query['gid']) : '';

        return "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv{$gid}";
    }

    /**
     * Parse CSV text into A/B/C-style row arrays to reuse existing column mapping/import logic.
     */
    private function parseCsvRowsToSheetShape(string $csv): array
    {
        $rows = [];
        $line = 1;

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return [];
        }

        fwrite($handle, $csv);
        rewind($handle);

        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null]) {
                continue;
            }

            $row = [];
            foreach ($data as $index => $value) {
                $row[$this->indexToColumnLetter($index)] = $value;
            }
            $rows[$line] = $row;
            $line++;
        }

        fclose($handle);

        return $rows;
    }

    private function indexToColumnLetter(int $index): string
    {
        $index += 1;
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }
        return $letters;
    }

    /**
     * Shared import pipeline for spreadsheet-like rows.
     */
    private function createInvoicesFromRows(array $rows): int
    {
        $header = array_map(fn ($v) => strtolower(trim((string) $v)), $rows[1] ?? []);
        $map = $this->mapImportColumns($header);
        $created = 0;

        foreach (array_slice($rows, 1) as $row) {
            $company = $row[$map['company']] ?? null;
            $amount = $row[$map['amount']] ?? null;
            if (!$company || !$amount) {
                continue;
            }

            $billTo = [
                'Company Name' => (string) $company,
                'Contact Name' => (string) ($row[$map['contact']] ?? ''),
                'Email' => (string) ($row[$map['email']] ?? ''),
                'Phone' => (string) ($row[$map['phone']] ?? ''),
                'Address' => (string) ($row[$map['address']] ?? ''),
            ];

            $invoiceDate = $this->parseDateValue($row[$map['invoice_date']] ?? null) ?? now();
            $dueDate = $this->parseDateValue($row[$map['due_date']] ?? null) ?? $invoiceDate->copy()->addDays(14);

            $invoiceNumber = $this->generateInvoiceNumber($billTo['Company Name']);
            $currencyCode = SettingService::getSetting('currency_code', 'USD');
            $currencyDisplay = SettingService::getSetting('currency_symbol', '$');

            $lineItems = [[
                'description' => 'Imported invoice',
                'quantity' => 1,
                'rate' => (float) $amount,
                'tax_label' => 'N/A',
                'line_total' => (float) $amount,
            ]];

            $subtotal = (float) $amount;
            $lineTaxTotal = 0.0;
            $invoiceTaxLines = [];
            $totalAmount = (float) $amount;
            $taxSummary = $this->buildTaxSummary(false, $subtotal, [], [], 0.0);

            $invoiceHtml = $this->renderInvoiceHtml([
                'invoiceNumber' => $invoiceNumber,
                'billTo' => $billTo,
                'lineItems' => $lineItems,
                'subtotal' => $subtotal,
                'lineTaxTotal' => $lineTaxTotal,
                'invoiceTaxLines' => $invoiceTaxLines,
                'taxSummary' => $taxSummary,
                'total' => $totalAmount,
                'currencyDisplay' => $currencyDisplay,
                'invoiceDate' => $invoiceDate->format('Y-m-d'),
                'dueDate' => $dueDate->format('Y-m-d'),
                'company' => $this->getCompanyInfo(),
                'titleBg' => '#FFDC00',
                'titleText' => '#0033D9',
            ]);

            $client = Client::firstOrCreate(
                ['company_name' => $billTo['Company Name']],
                [
                    'representative' => $billTo['Contact Name'] ?: null,
                    'email' => $billTo['Email'] ?: null,
                    'phone' => $billTo['Phone'] ?: null,
                    'address' => $billTo['Address'] ?: null,
                    'created_by' => Auth::id(),
                ]
            );

            $invoicePayload = [
                'invoice_number' => $invoiceNumber,
                'client_id' => $client->id,
                'bill_to_name' => $billTo['Company Name'],
                'bill_to_json' => $billTo,
                'total_amount' => $totalAmount,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => 'Unpaid',
                'html' => $invoiceHtml,
                'created_by' => Auth::id(),
                'currency_code' => $currencyCode,
                'currency_display' => $currencyDisplay,
                'invoice_title_bg' => '#FFDC00',
                'invoice_title_text' => '#0033D9',
            ];
            if ($this->invoiceTaxSummaryColumnExists()) {
                $invoicePayload['invoice_tax_summary'] = $taxSummary;
            }
            $invoice = Invoice::create($invoicePayload);
            $defaultReminderBindings = $this->buildDefaultReminderBindings(ReminderRuleService::getRules());
            if (!empty($defaultReminderBindings)) {
                $this->persistInvoiceEmailConfiguration($invoice, null, null, $defaultReminderBindings);
            }

            $this->generatePdf($invoice);
            $created++;
        }

        return $created;
    }

    /**
     * Prepare parsed import data in session for reference-style price selection step.
     */
    private function prepareImportSessionData(Request $request, array $rows, array $billTo): void
    {
        [$headers, $items] = $this->extractHeaderAndItemsFromRows($rows);

        $request->session()->put(InvoiceValidationContract::SESSION_IMPORT, [
            'headers' => $headers,
            'items' => $items,
            'bill_to' => [
                'Company Name' => (string) ($billTo['Company Name'] ?? ''),
                'Contact Name' => (string) ($billTo['Contact Name'] ?? ''),
                'Address' => (string) ($billTo['Address'] ?? ''),
                'Phone' => (string) ($billTo['Phone'] ?? ''),
                'Email' => (string) ($billTo['Email'] ?? ''),
            ],
        ]);
    }

    /**
     * Parse raw sheet rows into filtered headers/items similar to reference parse_excel.php.
     */
    private function extractHeaderAndItemsFromRows(array $rows): array
    {
        $headerRow = $rows[1] ?? [];
        if (empty($headerRow)) {
            return [[], []];
        }

        $indexes = array_keys($headerRow);
        $rawHeaders = [];
        foreach ($indexes as $index) {
            $rawHeaders[] = trim((string) ($headerRow[$index] ?? ''));
        }

        $keepByPos = [];
        foreach ($rawHeaders as $pos => $header) {
            if ($header !== '') {
                $keepByPos[$pos] = true;
                continue;
            }

            $hasData = false;
            foreach (array_slice($rows, 1) as $row) {
                $value = trim((string) ($row[$indexes[$pos]] ?? ''));
                if ($value !== '') {
                    $hasData = true;
                    break;
                }
            }
            $keepByPos[$pos] = $hasData;
        }

        $headers = [];
        foreach ($rawHeaders as $pos => $header) {
            if ($keepByPos[$pos] ?? false) {
                $headers[] = $header !== '' ? $header : ('Column ' . ($pos + 1));
            }
        }

        $items = [];
        foreach (array_slice($rows, 1) as $row) {
            $filteredValues = [];
            foreach ($indexes as $pos => $index) {
                if (!($keepByPos[$pos] ?? false)) {
                    continue;
                }
                $filteredValues[] = trim((string) ($row[$index] ?? ''));
            }

            $nonEmpty = array_filter($filteredValues, fn ($v) => $v !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $header) {
                $assoc[$header] = $filteredValues[$i] ?? '';
            }
            $items[] = $assoc;
        }

        return [$headers, $items];
    }

    /**
     * Parse amounts from imported data (supports commas, symbols, spaces).
     */
    private function parseImportedAmount($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = (string) $value;
        $str = str_replace(["\xC2\xA0", ' '], '', $str);

        if (str_contains($str, ',') && str_contains($str, '.')) {
            $str = str_replace(',', '', $str);
        } else {
            $str = str_replace(',', '.', $str);
        }

        $str = preg_replace('/[^0-9.\-]/', '', $str) ?? '';
        return is_numeric($str) ? (float) $str : 0.0;
    }

    private function parseDateValue($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
