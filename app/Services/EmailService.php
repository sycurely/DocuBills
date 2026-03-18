<?php

namespace App\Services;

use App\Jobs\SendInvoiceDeliveryEmailJob;
use App\Jobs\SendInvoiceReminderEmailJob;
use App\Jobs\SendPaymentConfirmationEmailJob;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailService
{
    /**
     * Return DB email setting keys that are currently missing.
     */
    public static function missingEmailSettings(): array
    {
        $provider = self::mailProvider();
        $required = [];

        if ($provider === 'sendgrid') {
            $required = [
                'sendgrid_api_key',
                'email_from_address',
            ];
        } elseif ($provider === 'log') {
            $required = [];
        } else {
            $required = [
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'email_from_address',
            ];
        }

        $missing = [];
        foreach ($required as $key) {
            $value = trim((string) SettingService::getSetting($key, ''));
            if ($value === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Backward-compatible alias for SMTP settings checks.
     */
    public static function missingDbSmtpSettings(): array
    {
        return self::missingEmailSettings();
    }

    /**
     * Queue invoice delivery email.
     */
    public static function sendInvoiceEmail(Invoice $invoice, ?string $pdfPath = null): bool
    {
        if (self::shouldSendSynchronously()) {
            return self::sendInvoiceDeliveryNow($invoice, $pdfPath);
        }

        SendInvoiceDeliveryEmailJob::dispatch($invoice->id, $pdfPath);
        return true;
    }

    /**
     * Queue payment confirmation email.
     */
    public static function sendPaymentConfirmationEmail(Invoice $invoice): bool
    {
        if (self::shouldSendSynchronously()) {
            return self::sendPaymentConfirmationNow($invoice);
        }

        SendPaymentConfirmationEmailJob::dispatch($invoice->id);
        return true;
    }

    /**
     * Queue reminder email.
     */
    public static function queueReminderEmail(Invoice $invoice, array $rule, int $templateId, string $statusSentScope): bool
    {
        SendInvoiceReminderEmailJob::dispatch($invoice->id, $rule, $templateId, $statusSentScope);
        return true;
    }

    private static function shouldSendSynchronously(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        $queueDefault = (string) (Config::get('queue.default') ?? '');
        if ($queueDefault === 'sync') {
            return true;
        }

        return true;
    }

    /**
     * Immediate invoice delivery sender for queue job.
     */
    public static function sendInvoiceDeliveryNow(Invoice $invoice, ?string $pdfPath = null): bool
    {
        $invoice->loadMissing('emailConfiguration');
        $recipient = self::resolveRecipient($invoice);
        if (!$recipient['email']) {
            Log::warning("Invoice delivery skipped, missing recipient: invoice {$invoice->id}");
            return false;
        }

        $template = TemplateResolutionService::resolveInvoiceDeliveryTemplate($invoice);
        $replacements = self::getInvoiceReplacements($invoice, $recipient['name']);

        $subject = $template?->subject ?: "Your Invoice {$invoice->invoice_number} from " . SettingService::getSetting('company_name', 'DocuBills');
        $body = $template ? $template->render($replacements)['body'] : self::defaultInvoiceDeliveryBody($invoice, $recipient['name']);
        if ($template) {
            $rendered = $template->render($replacements);
            $subject = $rendered['subject'];
            $body = $rendered['body'];
        }

        return self::sendHtmlMail(
            toEmail: $recipient['email'],
            toName: $recipient['name'],
            subject: $subject,
            body: $body,
            ccList: self::parseEmailList($template?->cc_emails),
            bccList: self::parseEmailList($template?->bcc_emails),
            invoice: $invoice,
            pdfPath: $pdfPath
        );
    }

    /**
     * Immediate payment confirmation sender for queue job.
     */
    public static function sendPaymentConfirmationNow(Invoice $invoice): bool
    {
        $invoice->loadMissing('emailConfiguration');
        $recipient = self::resolveRecipient($invoice);
        if (!$recipient['email']) {
            Log::warning("Payment confirmation skipped, missing recipient: invoice {$invoice->id}");
            return false;
        }

        $template = TemplateResolutionService::resolvePaymentConfirmationTemplate($invoice);
        $replacements = self::getInvoiceReplacements($invoice, $recipient['name']);

        $subject = $template?->subject ?: "Payment Received: Invoice {$invoice->invoice_number}";
        $body = $template ? $template->render($replacements)['body'] : self::defaultPaymentConfirmationBody($invoice, $recipient['name']);
        if ($template) {
            $rendered = $template->render($replacements);
            $subject = $rendered['subject'];
            $body = $rendered['body'];
        }

        return self::sendHtmlMail(
            toEmail: $recipient['email'],
            toName: $recipient['name'],
            subject: $subject,
            body: $body,
            ccList: self::parseEmailList($template?->cc_emails),
            bccList: self::parseEmailList($template?->bcc_emails)
        );
    }

    /**
     * Immediate reminder sender for queue job.
     */
    public static function sendReminderNow(
        Invoice $invoice,
        array $rule,
        int $templateId,
        string $statusSentScope,
        array $extraReplacements = []
    ): bool
    {
        $ruleId = (string) ($rule['id'] ?? '');
        if ($ruleId === '') {
            Log::warning("Reminder send aborted: missing rule id for invoice {$invoice->id}");
            return false;
        }

        // Idempotency guard before send.
        $alreadySent = InvoiceReminderLog::query()
            ->where('invoice_id', $invoice->id)
            ->where('reminder_type', $ruleId)
            ->where('rule_id', $ruleId)
            ->where('template_id', $templateId)
            ->whereDate('status_sent_scope', $statusSentScope)
            ->where('status', 'sent')
            ->exists();
        if ($alreadySent) {
            return true;
        }

        $recipient = self::resolveRecipient($invoice);
        if (!$recipient['email']) {
            self::logReminderResult($invoice->id, $ruleId, $templateId, $statusSentScope, null, 'failed', 'No recipient email found');
            return false;
        }

        $payload = self::buildReminderPayload($invoice, $rule, $templateId, $recipient['name'], $extraReplacements);
        $subject = $payload['subject'];
        $body = $payload['body'];
        $ccList = $payload['cc'];
        $bccList = $payload['bcc'];
        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";

        try {
            $sent = self::sendHtmlMail(
                toEmail: $recipient['email'],
                toName: $recipient['name'],
                subject: $subject,
                body: $body,
                ccList: $ccList,
                bccList: $bccList,
                invoice: $invoice,
                pdfPath: $pdfPath
            );

            self::logReminderResult(
                $invoice->id,
                $ruleId,
                $templateId,
                $statusSentScope,
                $recipient['email'],
                $sent ? 'sent' : 'failed',
                $sent ? null : 'Mail transport returned false'
            );

            return $sent;
        } catch (\Throwable $e) {
            self::logReminderResult(
                $invoice->id,
                $ruleId,
                $templateId,
                $statusSentScope,
                $recipient['email'],
                'failed',
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Build reminder email preview payload without sending.
     */
    public static function buildReminderPreview(Invoice $invoice, array $rule, ?int $templateId = null): array
    {
        $recipient = self::resolveRecipient($invoice);
        return [
            'recipient' => $recipient,
            ...self::buildReminderPayload($invoice, $rule, $templateId, $recipient['name']),
        ];
    }

    /**
     * Send an already-rendered reminder immediately to a specific recipient.
     */
    public static function sendRenderedReminderNow(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?Invoice $invoice = null,
        ?string $pdfPath = null
    ): bool {
        return self::sendHtmlMail(
            toEmail: $toEmail,
            toName: $toName,
            subject: $subject,
            body: $body,
            ccList: $ccList,
            bccList: $bccList,
            invoice: $invoice,
            pdfPath: $pdfPath
        );
    }

    private static function configureMail(): void
    {
        $smtpHost = SettingService::getSetting('smtp_host');
        $smtpPort = SettingService::getSetting('smtp_port', '587');
        $smtpUsername = SettingService::getSetting('smtp_username');
        $smtpPassword = SettingService::getSetting('smtp_password');
        $fromName = SettingService::getSetting('email_from_name', 'DocuBills');
        $fromAddress = SettingService::getSetting('email_from_address');

        if ($smtpHost && $smtpUsername && $smtpPassword) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $smtpHost);
            Config::set('mail.mailers.smtp.port', $smtpPort);
            Config::set('mail.mailers.smtp.username', $smtpUsername);
            Config::set('mail.mailers.smtp.password', $smtpPassword);
            Config::set('mail.mailers.smtp.encryption', (int) $smtpPort === 465 ? 'ssl' : 'tls');
        }

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
        }
    }

    private static function sendHtmlMail(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?Invoice $invoice = null,
        ?string $pdfPath = null
    ): bool {
        $provider = self::mailProvider();
        if ($provider === 'log') {
            self::logEmailPayload($toEmail, $toName, $subject, $body, $ccList, $bccList, $invoice, $pdfPath);
            return true;
        }

        if ($provider === 'sendgrid') {
            return self::sendHtmlMailSendGrid(
                toEmail: $toEmail,
                toName: $toName,
                subject: $subject,
                body: $body,
                ccList: $ccList,
                bccList: $bccList,
                invoice: $invoice,
                pdfPath: $pdfPath
            );
        }

        self::configureMail();

        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $subject, $body, $ccList, $bccList, $invoice, $pdfPath) {
                $message->to($toEmail, $toName)->subject($subject)->html($body);

                foreach ($ccList as $cc) {
                    $message->cc($cc);
                }
                foreach ($bccList as $bcc) {
                    $message->bcc($bcc);
                }

                if ($invoice && $pdfPath && Storage::disk('invoices')->exists($pdfPath)) {
                    $message->attach(Storage::disk('invoices')->path($pdfPath), [
                        'as' => "{$invoice->invoice_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Email send failed', [
                'to' => $toEmail,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private static function logEmailPayload(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?Invoice $invoice = null,
        ?string $pdfPath = null
    ): void {
        $attachment = null;
        if ($invoice && $pdfPath && Storage::disk('invoices')->exists($pdfPath)) {
            $attachment = [
                'path' => $pdfPath,
                'filename' => "{$invoice->invoice_number}.pdf",
            ];
        }

        $payload = [
            'to' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'cc' => $ccList,
            'bcc' => $bccList,
            'attachment' => $attachment,
            'body' => $body,
        ];

        Log::info('Email log (provider=log)', $payload);

        $line = '[' . now()->toDateTimeString() . '] ' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        @file_put_contents(storage_path('logs/mail.log'), $line . PHP_EOL, FILE_APPEND);
    }

    private static function sendHtmlMailSendGrid(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?Invoice $invoice = null,
        ?string $pdfPath = null
    ): bool {
        $apiKey = trim((string) SettingService::getSetting('sendgrid_api_key', ''));
        if ($apiKey === '') {
            Log::error('SendGrid API key is missing.');
            return false;
        }

        $fromEmail = trim((string) SettingService::getSetting('email_from_address', ''));
        $fromName = trim((string) SettingService::getSetting('email_from_name', 'DocuBills'));
        if ($fromEmail === '') {
            Log::error('SendGrid from email is missing.');
            return false;
        }

        $payload = [
            'personalizations' => [[
                'to' => [[
                    'email' => $toEmail,
                    'name' => $toName,
                ]],
                'subject' => $subject,
            ]],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'content' => [[
                'type' => 'text/html',
                'value' => $body,
            ]],
        ];

        if (!empty($ccList)) {
            $payload['personalizations'][0]['cc'] = array_map(fn ($cc) => ['email' => $cc], $ccList);
        }
        if (!empty($bccList)) {
            $payload['personalizations'][0]['bcc'] = array_map(fn ($bcc) => ['email' => $bcc], $bccList);
        }

        if ($invoice && $pdfPath && Storage::disk('invoices')->exists($pdfPath)) {
            $content = base64_encode(Storage::disk('invoices')->get($pdfPath));
            $payload['attachments'] = [[
                'content' => $content,
                'type' => 'application/pdf',
                'filename' => "{$invoice->invoice_number}.pdf",
                'disposition' => 'attachment',
            ]];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->post('https://api.sendgrid.com/v3/mail/send', $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('SendGrid email send failed', [
                'to' => $toEmail,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('SendGrid email send exception', [
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private static function mailProvider(): string
    {
        $provider = strtolower(trim((string) SettingService::getSetting('email_provider', 'smtp')));
        return $provider !== '' ? $provider : 'smtp';
    }

    private static function resolveRecipient(Invoice $invoice): array
    {
        $invoice->loadMissing('client');
        $billTo = $invoice->bill_to_json ?? [];

        $email = (string) ($billTo['Email'] ?? ($invoice->client->email ?? ''));
        $name = (string) ($billTo['Contact Name'] ?? $billTo['Company Name'] ?? ($invoice->client->company_name ?? 'Valued Client'));

        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['email' => null, 'name' => $name];
        }

        return ['email' => $email, 'name' => $name];
    }

    private static function getInvoiceReplacements(Invoice $invoice, string $clientName): array
    {
        $companyName = SettingService::getSetting('company_name', 'DocuBills');
        $currencyDisplay = $invoice->currency_display ?: ($invoice->currency_code ?: '$');

        return [
            'client_name' => $clientName,
            'invoice_number' => (string) $invoice->invoice_number,
            'company_name' => $companyName,
            'total_amount' => $currencyDisplay . ' ' . number_format((float) $invoice->total_amount, 2),
            'amount_due' => $currencyDisplay . ' ' . number_format((float) $invoice->total_amount, 2),
            'payment_link' => (string) ($invoice->payment_link ?: '#'),
            'due_date' => optional($invoice->due_date)->format('Y-m-d') ?: 'N/A',
            'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d') ?: optional($invoice->created_at)->format('Y-m-d'),
            'reminder_type' => '',
        ];
    }

    private static function parseEmailList(?string $raw, int $max = 10): array
    {
        if (empty($raw)) {
            return [];
        }

        $normalized = str_replace(["\r", "\n", "\t", ";"], [",", ",", ",", ","], $raw);
        $parts = array_map('trim', explode(',', $normalized));

        $emails = [];
        foreach ($parts as $email) {
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emails[strtolower($email)] = $email;
            if (count($emails) >= $max) {
                break;
            }
        }

        return array_values($emails);
    }

    private static function logReminderResult(
        int $invoiceId,
        string $ruleId,
        int $templateId,
        string $statusSentScope,
        ?string $recipientEmail,
        string $status,
        ?string $error = null
    ): void {
        try {
            InvoiceReminderLog::query()->updateOrCreate(
                [
                    'invoice_id' => $invoiceId,
                    'reminder_type' => $ruleId,
                    'rule_id' => $ruleId,
                    'template_id' => $templateId,
                    'status_sent_scope' => $statusSentScope,
                ],
                [
                    'sent_at' => now(),
                    'recipient_email' => $recipientEmail ?: 'unknown',
                    'status' => $status,
                    'error_message' => $error,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to write reminder log', [
                'invoice_id' => $invoiceId,
                'rule_id' => $ruleId,
                'template_id' => $templateId,
                'scope' => $statusSentScope,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function buildReminderPayload(
        Invoice $invoice,
        array $rule,
        ?int $templateId,
        string $recipientName,
        array $extraReplacements = []
    ): array
    {
        $template = null;
        if (!empty($templateId)) {
            $template = EmailTemplate::query()
                ->where('id', (int) $templateId)
                ->whereNull('deleted_at')
                ->first();
        }
        $replacements = array_merge(self::getInvoiceReplacements($invoice, $recipientName), $extraReplacements);
        $replacements['reminder_type'] = ReminderRuleService::reminderTypeLabel($rule);

        if ($template) {
            $rendered = $template->render($replacements);
            return [
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'cc' => self::parseEmailList($template->cc_emails),
                'bcc' => self::parseEmailList($template->bcc_emails),
            ];
        }

        return [
            'subject' => "Payment Reminder: Invoice {$invoice->invoice_number}",
            'body' => self::defaultReminderBody(
                $invoice,
                $recipientName,
                (string) $replacements['reminder_type'],
                (string) ($replacements['reminder_scheduled_for'] ?? '')
            ),
            'cc' => [],
            'bcc' => [],
        ];
    }

    private static function defaultInvoiceDeliveryBody(Invoice $invoice, string $clientName): string
    {
        $currencyDisplay = $invoice->currency_display ?: ($invoice->currency_code ?: '$');
        $companyName = (string) SettingService::getSetting('company_name', 'DocuBills');
        $companyEmail = trim((string) SettingService::getSetting('email_from_address', ''));
        $companyLogo = self::resolveCompanyLogoMarkup();
        $invoiceDate = optional($invoice->invoice_date)->format('Y-m-d') ?: 'N/A';
        $dueDate = optional($invoice->due_date)->format('Y-m-d') ?: 'N/A';
        $paymentLink = trim((string) ($invoice->payment_link ?? ''));
        $invoiceFallback = url('/invoices/' . $invoice->id);
        $hasPaymentLink = $paymentLink !== '';
        $payNowHref = $hasPaymentLink ? $paymentLink : $invoiceFallback;
        $payNowLabel = $hasPaymentLink ? 'View / Pay Invoice' : 'View Invoice';
        $payNowStyle = 'background:#16a34a;color:#ffffff;';
        $logoLine = $companyLogo !== '' ? $companyLogo : '';
        $contactLine = $companyEmail !== ''
            ? sprintf('If you have any questions, reply to this email or contact %s.', e($companyEmail))
            : 'If you have any questions, reply to this email.';

        return sprintf(
            '<div style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;">
              <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="padding:24px 12px;">
                <tr>
                  <td align="center">
                    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                      <tr>
                        <td style="padding:20px 24px;background:#0f172a;color:#ffffff;">
                          <table role="presentation" width="100%%" cellpadding="0" cellspacing="0">
                            <tr>
                              <td style="vertical-align:middle;">
                                <div style="font-size:18px;font-weight:700;">Invoice Ready</div>
                                <div style="font-size:13px;opacity:0.85;">%s</div>
                              </td>
                              <td align="right" style="vertical-align:middle;">
                                %s
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:22px 24px;color:#0f172a;">
                          <p style="margin:0 0 12px;">Hello <strong>%s</strong>,</p>
                          <p style="margin:0 0 12px;">Thank you for your business. Your invoice <strong>%s</strong> is ready.</p>
                          <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="margin:12px 0 16px;border:1px solid #e2e8f0;border-radius:10px;">
                            <tr>
                              <td style="padding:12px 14px;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Invoice Date</div>
                                <div style="font-size:16px;font-weight:600;color:#0f172a;">%s</div>
                              </td>
                              <td style="padding:12px 14px;border-left:1px solid #e2e8f0;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Due Date</div>
                                <div style="font-size:16px;font-weight:600;color:#0f172a;">%s</div>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding:12px 14px;border-top:1px solid #e2e8f0;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Invoice Total</div>
                                <div style="font-size:18px;font-weight:700;color:#0f172a;">%s %s</div>
                              </td>
                              <td style="padding:12px 14px;border-left:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Invoice Number</div>
                                <div style="font-size:16px;font-weight:600;color:#0f172a;">%s</div>
                              </td>
                            </tr>
                          </table>
                          <div style="text-align:center;margin:18px 0 10px;">
                            <a href="%s" style="display:inline-block;padding:12px 22px;border-radius:999px;text-decoration:none;font-weight:700;%s">%s</a>
                          </div>
                          <p style="margin:0;color:#475569;font-size:13px;">%s</p>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:14px 24px;background:#f1f5f9;color:#64748b;font-size:12px;">
                          %s
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </div>',
            e($companyName),
            $logoLine,
            e($clientName),
            e($invoice->invoice_number),
            e($invoiceDate),
            e($dueDate),
            e($currencyDisplay),
            number_format((float) $invoice->total_amount, 2),
            e($invoice->invoice_number),
            e($payNowHref),
            $payNowStyle,
            e($payNowLabel),
            $contactLine,
            'Powered by DocuBills'
        );
    }

    private static function defaultPaymentConfirmationBody(Invoice $invoice, string $clientName): string
    {
        return sprintf(
            '<p>Dear <strong>%s</strong>,</p><p>Payment has been received for invoice <strong>%s</strong>.</p><p>Thank you.</p>',
            e($clientName),
            e($invoice->invoice_number)
        );
    }

    private static function defaultReminderBody(
        Invoice $invoice,
        string $clientName,
        string $reminderType,
        string $scheduledFor = ''
    ): string
    {
        $currencyDisplay = $invoice->currency_display ?: ($invoice->currency_code ?: '$');
        $companyName = (string) SettingService::getSetting('company_name', 'DocuBills');
        $companyLogo = self::resolveCompanyLogoMarkup();
        $paymentLink = trim((string) ($invoice->payment_link ?? ''));
        $invoiceFallback = url('/invoices/' . $invoice->id);
        $hasPaymentLink = $paymentLink !== '';
        $payNowHref = $hasPaymentLink ? $paymentLink : $invoiceFallback;
        $payNowLabel = $hasPaymentLink ? 'Pay Now' : 'View Invoice';
        $payNowStyle = 'background:#2563eb;color:#ffffff;';
        $scheduledLine = '';
        if (trim($scheduledFor) !== '') {
            $scheduledLine = sprintf('<p style="margin:0 0 12px;">Scheduled reminder date: <strong>%s</strong></p>', e($scheduledFor));
        }
        $logoLine = $companyLogo !== '' ? $companyLogo : '';

        return sprintf(
            '<div style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;">
              <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="padding:24px 12px;">
                <tr>
                  <td align="center">
                    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                      <tr>
                        <td style="padding:20px 24px;background:#0f172a;color:#ffffff;">
                          <table role="presentation" width="100%%" cellpadding="0" cellspacing="0">
                            <tr>
                              <td style="vertical-align:middle;">
                                <div style="font-size:18px;font-weight:700;">Payment Reminder</div>
                                <div style="font-size:13px;opacity:0.85;">%s</div>
                              </td>
                              <td align="right" style="vertical-align:middle;">
                                %s
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:22px 24px;color:#0f172a;">
                          <p style="margin:0 0 12px;">Dear <strong>%s</strong>,</p>
                          <p style="margin:0 0 12px;">This is a reminder (%s) for invoice <strong>%s</strong>.</p>
                          %s
                          <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="margin:12px 0 16px;border:1px solid #e2e8f0;border-radius:10px;">
                            <tr>
                              <td style="padding:12px 14px;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Amount Due</div>
                                <div style="font-size:18px;font-weight:700;color:#0f172a;">%s %s</div>
                              </td>
                              <td style="padding:12px 14px;border-left:1px solid #e2e8f0;">
                                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Due Date</div>
                                <div style="font-size:16px;font-weight:600;color:#0f172a;">%s</div>
                              </td>
                            </tr>
                          </table>
                          <div style="text-align:center;margin:18px 0 10px;">
                            <a href="%s" style="display:inline-block;padding:12px 22px;border-radius:999px;text-decoration:none;font-weight:700;%s">%s</a>
                          </div>
                          <p style="margin:0;color:#475569;font-size:13px;">If you have already paid, please ignore this reminder.</p>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:14px 24px;background:#f1f5f9;color:#64748b;font-size:12px;">
                          %s
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </div>',
            e($reminderType),
            $logoLine,
            e($clientName),
            e($reminderType),
            e($invoice->invoice_number),
            $scheduledLine,
            e($currencyDisplay),
            number_format((float) $invoice->total_amount, 2),
            e(optional($invoice->due_date)->format('Y-m-d') ?: 'N/A'),
            e($payNowHref),
            $payNowStyle,
            e($payNowLabel),
            'Powered by DocuBills'
        );
    }

    private static function resolveCompanyLogoMarkup(): string
    {
        $logo = trim((string) SettingService::getSetting('company_logo', ''));
        if ($logo === '') {
            $logo = 'homepage/images/docubills-logo.png';
        }
        if ($logo === '') {
            return '';
        }

        if (preg_match('/^(https?:)?\\/\\//i', $logo) || str_starts_with($logo, 'data:image/')) {
            return sprintf(
                '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
                e($logo),
                e((string) SettingService::getSetting('company_name', 'DocuBills'))
            );
        }

        $logo = ltrim($logo, '/');
        if (preg_match('/^[a-zA-Z]:\\\\/', $logo) || str_starts_with($logo, '/')) {
            $absolutePath = $logo;
        } else {
            $absolutePath = public_path($logo);
        }

        if (is_file($absolutePath)) {
            $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $normalizedAbsolute = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
            $normalizedPublic = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $publicRoot);
            if (str_starts_with($normalizedAbsolute, $normalizedPublic)) {
                $relativePath = ltrim(str_replace('\\', '/', substr($normalizedAbsolute, strlen($normalizedPublic))), '/');
                if ($relativePath !== '') {
                    return sprintf(
                        '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
                        e(url('/' . $relativePath)),
                        e((string) SettingService::getSetting('company_name', 'DocuBills'))
                    );
                }
            }

            $inline = self::fileToDataUri($absolutePath);
            if ($inline !== '') {
                return sprintf(
                    '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
                    e($inline),
                    e((string) SettingService::getSetting('company_name', 'DocuBills'))
                );
            }

            return sprintf(
                '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
                e(url('/' . ltrim($logo, '/'))),
                e((string) SettingService::getSetting('company_name', 'DocuBills'))
            );
        }

        // If only a filename was provided, try common public locations.
        if (!str_contains($logo, '/')) {
            $candidates = [
                'homepage/images/' . $logo,
                'assets/' . $logo,
            ];
            foreach ($candidates as $candidate) {
                if (is_file(public_path($candidate))) {
                    return sprintf(
                        '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
                        e(url('/' . $candidate)),
                        e((string) SettingService::getSetting('company_name', 'DocuBills'))
                    );
                }
            }
        }

        return sprintf(
            '<img src="%s" alt="%s" style="height:36px;max-width:160px;object-fit:contain;display:block;">',
            e(url('/' . ltrim($logo, '/'))),
            e((string) SettingService::getSetting('company_name', 'DocuBills'))
        );
    }

    private static function fileToDataUri(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $contents = @file_get_contents($path);
        if ($contents === false || $contents === '') {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
