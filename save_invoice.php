<?php
// Start output buffering BEFORE loading config.php
// so any accidental output (whitespace, BOM, notices) is captured
if (!ob_get_level()) {
    ob_start();
}

require_once 'config.php';
require_once 'mailer.php';

// Composer autoloader (Stripe, etc.)
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log("Composer autoload not found at {$composerAutoload}");
}

// DomPDF autoloader for PDF generation
$dompdfAutoload = __DIR__ . '/libs/dompdf/autoload.inc.php';
if (file_exists($dompdfAutoload)) {
    require_once $dompdfAutoload;
} else {
    error_log("DomPDF autoload not found at {$dompdfAutoload}");
}

// DEBUG: show all errors for now on this page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Also log to a local file in case hosting hides display_errors
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/save_invoice_error.log');

$invoiceDir = __DIR__ . '/invoices';
if (!is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0775, true);
}

use Dompdf\Dompdf;
use Stripe\Stripe;
use Stripe\Checkout\Session;

// Start session safely AFTER buffering and config include
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'middleware.php';

// 🔐 If session has expired, redirect to login before any permission checks
if (empty($_SESSION['user_id'])) {
    if (ob_get_level()) {
        ob_end_clean(); // clear any buffered output so headers work
    }
    header('Location: login.php?error=' . urlencode('Your session has expired. Please log in again.'));
    exit;
}

// Who is creating this invoice?
$currentUserId        = $_SESSION['user_id'] ?? null;
$can_email_invoice    = has_permission('email_invoice');
$can_manage_recurring = has_permission('manage_recurring_invoices');
$data                 = $_SESSION['invoice_data'] ?? null;

if (!$data) {
    // Fallback: rebuild minimal invoice data from POST if session is empty
    $bill_to_post = $_POST['bill_to'] ?? [];

    $fallbackBillTo = [
        'Company Name'   => $bill_to_post['Company Name']   ?? '',
        'Contact Name'   => $bill_to_post['Contact Name']   ?? '',
        'Address'        => $bill_to_post['Address']        ?? '',
        'Phone'          => $bill_to_post['Phone']          ?? '',
        'Email'          => $bill_to_post['Email']          ?? '',
        'gst_hst'        => $bill_to_post['gst_hst']        ?? '',
        'notes'          => $bill_to_post['notes']          ?? '',
    ];

    $data = [
        'bill_to' => $fallbackBillTo,
        // Items are already baked into invoice_html; we can leave this empty safely.
        'items'   => [],
        'total'   => floatval($_POST['invoice_total'] ?? 0),
    ];
}

// Validate invoice date
$invoice_date_raw = trim($_POST['invoice_date'] ?? '') . ' ' . trim($_POST['invoice_time'] ?? '');
if (!strtotime($invoice_date_raw)) {
    exit('❌ Invalid invoice date or time. Please go back and correct it.');
}

$date = date('Y-m-d H:i:s', strtotime($invoice_date_raw));
$clientName = $data['bill_to']['Company Name'] ?? '';
$client = $clientName;
$status = 'Unpaid';

// ─────────────────────────────────────────────
// ✅ Currency (lock it per invoice) — ISO code only
// ─────────────────────────────────────────────
$settingsCurrencyCode = strtoupper(trim((string) get_setting('currency_code')));
if ($settingsCurrencyCode === '') {
    $settingsCurrencyCode = 'CAD';
}

// Prefer POST, else settings
$currency_code = strtoupper(trim((string)($_POST['currency_code'] ?? $settingsCurrencyCode)));

// Validate (3–10 chars, letters/numbers). If invalid, fallback to settings.
if ($currency_code === '' || !preg_match('/^[A-Z0-9]{3,10}$/', $currency_code)) {
    $currency_code = $settingsCurrencyCode;
}

// ✅ Force display to ISO-like (prevents weird symbols/encoding issues everywhere)
$currency_display = strtoupper(trim((string)($_POST['currency_display'] ?? '')));
if ($currency_display === '' || !preg_match('/^[A-Z0-9]{3,10}$/', $currency_display)) {
    $currency_display = $currency_code;
}

// ─────────────────────────────────────────────
// ✅ Invoice Title Bar Color (store per invoice)
// ─────────────────────────────────────────────
$allowedTitleBarColors = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];

// Prefer POST; fallback to default
$invoice_title_bg = strtoupper(trim((string)($_POST['invoice_title_bg'] ?? '#FFDC00')));
if (!in_array($invoice_title_bg, $allowedTitleBarColors, true)) {
    $invoice_title_bg = '#FFDC00';
}

// ✅ Text color rule: Yellow => Blue text (#0033D9), otherwise White
$invoice_title_text = ($invoice_title_bg === '#FFDC00') ? '#0033D9' : '#FFFFFF';

// ─────────────────────────────────────────────
// Pricing mode + invoice total (trusted amount)
// ─────────────────────────────────────────────

// Configuration stored by price_select.php (if used)
$priceCfg        = $_SESSION['price_config'] ?? null;

// Detect pricing mode: 'column' (auto) vs 'manual'
$price_mode_post = $_POST['price_mode'] ?? ($priceCfg['price_mode'] ?? 'column');
$manual_mode     = ($price_mode_post === 'manual');

// Get invoice total with a clear precedence:
// 1) Trusted hidden field from generate_invoice.php
// 2) pre_total from price_select.php (auto mode only)
// 3) Fallback to $_SESSION['invoice_data']['total']
if (isset($_POST['invoice_total']) && $_POST['invoice_total'] !== '') {
    $invoice_total = (float) $_POST['invoice_total'];
} elseif (!$manual_mode && $priceCfg && isset($priceCfg['pre_total'])) {
    $invoice_total = (float) $priceCfg['pre_total'];
} else {
    $invoice_total = (float) ($data['total'] ?? 0);
}

// Normalise to 2 decimals for Stripe / display
$invoice_total = round($invoice_total, 2);

error_log(
    '💰 save_invoice.php - mode=' . $price_mode_post .
    ' invoice_total=' . $invoice_total .
    ' pre_total_from_cfg=' . ($priceCfg['pre_total'] ?? 'n/a')
);

// Handle due date
$raw_date = $_POST['due_date'] ?? '';
$raw_time = $_POST['due_time'] ?? '';
$include_time = isset($_POST['include_due_time']);
$due_date = '';

if ($raw_date) {
    try {
        if ($include_time && $raw_time) {
            $dt = new DateTime($raw_date . ' ' . $raw_time);
            $due_date = $dt->format('Y-m-d H:i');
        } else {
            $dt = new DateTime($raw_date);
            $due_date = $dt->format('Y-m-d');
        }
    } catch (Exception $e) {
        $due_date = '';
    }
} else {
    $due_date = date('Y-m-d', strtotime($date . ' +14 days'));
}

// Recurring invoice flag (from generate_invoice.php + permission guard)
$raw_is_recurring = isset($_POST['is_recurring']) && $_POST['is_recurring'] === '1';

// Only allow recurring if the user has the manage_recurring_invoices permission
if ($can_manage_recurring && $raw_is_recurring) {
    $is_recurring = 1;
} else {
    $is_recurring = 0;
}

$recurrence_type = $is_recurring ? 'monthly' : null;
$next_run_date   = null;

if ($is_recurring) {
    try {
        // $date is the invoice date/time we already computed above (Y-m-d H:i:s)
        $invoiceDt = new DateTime($date);
        $invoiceDt->modify('+1 month');
        // We only care about the DATE for scheduling (cron will run daily)
        $next_run_date = $invoiceDt->format('Y-m-d');
    } catch (Exception $e) {
        error_log('❌ Error computing next_run_date for recurring invoice: ' . $e->getMessage());
        $next_run_date = null;
    }
}

// Show / hide the Banking Details block on final PDF
// 1 = show, 0 = hide. Default 1 keeps legacy behaviour if POST is missing.
$show_bank_details = isset($_POST['show_bank_details'])
    ? (int) $_POST['show_bank_details']
    : 1;

// Banking details submitted with invoice (override settings per invoice)
$bank_account_name    = trim($_POST['bank_account_name']    ?? '');
$bank_name            = trim($_POST['bank_name']            ?? '');
$bank_account_number  = trim($_POST['bank_account_number']  ?? '');
$bank_iban            = trim($_POST['bank_iban']            ?? '');
$bank_swift           = trim($_POST['bank_swift']           ?? '');
$bank_routing         = trim($_POST['bank_routing']         ?? '');
$bank_additional_info = trim($_POST['bank_additional_info'] ?? '');

// Also attach to $data array so template_invoice.php can access via $data['...']
$data['bank_account_name']    = $bank_account_name;
$data['bank_name']            = $bank_name;
$data['bank_account_number']  = $bank_account_number;
$data['bank_iban']            = $bank_iban;
$data['bank_swift']           = $bank_swift;
$data['bank_routing']         = $bank_routing;
$data['bank_additional_info'] = $bank_additional_info;
$data['show_bank_details']    = $show_bank_details;
$data['is_recurring']         = $is_recurring;
$data['recurrence_type']      = $recurrence_type;
$data['next_run_date']        = $next_run_date;

// Prepare HTML safely for DB (special characters, encoding)
$invoiceHtmlForDb = $_POST['invoice_html'] ?? '';

if (!is_string($invoiceHtmlForDb)) {
    $invoiceHtmlForDb = (string)$invoiceHtmlForDb;
}

// If mbstring is available, ensure UTF-8 encoding to avoid DB charset issues
if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
    if (!mb_detect_encoding($invoiceHtmlForDb, 'UTF-8', true)) {
        $invoiceHtmlForDb = mb_convert_encoding($invoiceHtmlForDb, 'UTF-8', 'auto');
    }
}

// Log length for debugging
error_log('🧾 save_invoice.php - invoice_html length: ' . strlen($invoiceHtmlForDb));

// ⛔ Guard: if total is invalid, stop before saving or creating Stripe session
if ($invoice_total <= 0) {
    error_log("❌ Invalid invoice_total ({$invoice_total}) in save_invoice.php.");

    if (ob_get_level()) {
        ob_end_clean();
    }

    if ($manual_mode) {
        // Manual pricing: user never entered a valid total
        exit(
            '❌ Invoice total is missing or invalid. ' .
            'Please go back to the invoice preview and enter a valid total amount.'
        );
    } else {
        // Automatic pricing: bounce back to price_select so they can choose a better column
        header('Location: price_select.php?error=' . urlencode(
            'The selected price column did not produce a valid total amount. ' .
            'Please choose a different price column that contains the line-item totals, or verify your data.'
        ));
        exit;
    }
}

// Flag from generate_invoice.php: 1 = manual only, do NOT create Stripe payment link
$skip_stripe = isset($_POST['skip_stripe']) && $_POST['skip_stripe'] === '1';

// Save client and invoice
try {
    $bill_to    = $data['bill_to'] ?? [];
    $billToJson = json_encode($bill_to, JSON_UNESCAPED_UNICODE);
    
    $clientName = $bill_to['Company Name'] ?? '';
    $email      = $bill_to['Email']       ?? '';
    $phone      = $bill_to['Phone']       ?? '';
    $address    = $bill_to['Address']     ?? '';
    $representative = $bill_to['Contact Name'] ?? '';
    $client_email = $bill_to['Email']        ?? '';
    $client_name  = $bill_to['Contact Name'] ?? $bill_to['Company Name'] ?? 'Valued Client';
    $gst_hst        = $bill_to['gst_hst'] ?? '';
    $notes          = $bill_to['notes']   ?? '';

    // Check if client exists
    $checkClient = $pdo->prepare("SELECT id FROM clients WHERE company_name = ?");
    $checkClient->execute([$clientName]);
    $clientId = $checkClient->fetchColumn();

    if ($clientId) {
        $updateClient = $pdo->prepare("
            UPDATE clients SET 
              email = ?, 
              phone = ?, 
              address = ?, 
              representative = ?, 
              gst_hst = ?, 
              notes = ?, 
              updated_at = NOW()
            WHERE id = ?
        ");
        $updateClient->execute([$email, $phone, $address, $representative, $gst_hst, $notes, $clientId]);
    } else {
        $insertClient = $pdo->prepare("
            INSERT INTO clients (
                company_name, representative, phone, email, address, gst_hst, notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $insertClient->execute([
            $clientName, $representative, $phone, $email, $address, $gst_hst, $notes
        ]);
        $clientId = $pdo->lastInsertId();
    }
    
     // ── Smart client-code + per-client sequence ───────────────────────────
     $prefix    = get_setting('invoice_prefix') ?: 'FIN';
    
     // 1) Clean & split into words, ignoring non-alphanumerics
     $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', strtoupper($clientName));
     $words     = preg_split('/\s+/', trim($cleanName));
    
     // 2) Build the client code:
     if (count($words) === 1) {
         // Single word: first 3 letters
         $clientCode = substr($words[0], 0, 3);
     } else {
         // 2–3 words: first letter of each word; >3 words: first letter of first 4 words
         $limit      = min(count($words), 4);
         $clientCode = '';
         for ($i = 0; $i < $limit; $i++) {
             $clientCode .= substr($words[$i], 0, 1);
         }
     }
    
     // 3) Determine next sequence number for this client
     $likePattern = "{$prefix}-{$clientCode}-%";
     $stmtSeq     = $pdo->prepare("
         SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) AS max_seq
           FROM invoices
          WHERE invoice_number LIKE ?
     ");
     $stmtSeq->execute([$likePattern]);
     $maxSeq   = (int) $stmtSeq->fetchColumn();
     $nextSeq  = $maxSeq + 1;
    
     // 4) Compose with zero-padded two-digit sequence
     $invoiceNumber = sprintf('%s-%s-%02d', $prefix, $clientCode, $nextSeq);
     // ─────────────────────────────────────────────────────────────────────────

    // Single INSERT for the invoice record (now tracking who created it + banking flag + recurring info)
    $stmt = $pdo->prepare(
    "INSERT INTO invoices
        (invoice_number, client_id, bill_to_name, bill_to_json, total_amount,
         created_at, invoice_date, due_date, status, html, created_by, show_bank_details,
         is_recurring, recurrence_type, next_run_date, currency_code, currency_display,
         invoice_title_bg, invoice_title_text)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $success = $stmt->execute([
        $invoiceNumber,
        $clientId,
        $client,
        $billToJson,
        $invoice_total,
        $date,
        $date,
        $due_date,
        $status,
        $invoiceHtmlForDb,
        $currentUserId,
        $show_bank_details,
        $is_recurring,
        $recurrence_type,
        $next_run_date,
        $currency_code,
        $currency_display,
        $invoice_title_bg,
        $invoice_title_text
    ]);

    if (! $success) {
        error_log("Failed to insert invoice: " . print_r($stmt->errorInfo(), true));
        exit('❌ Invoice could not be saved. Please try again.');
    }

    // (Optional) Fetch the new invoice ID if you need it later
    $invoice_id = $pdo->lastInsertId();

} catch (Exception $e) {
    $msg = $e->getMessage();
    error_log("❌ Client/invoice sync failed: " . $msg);

    // Show the root cause on screen (for now while debugging)
    exit('❌ Error saving invoice data: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
}

// Handle payment link (Stripe / Test / Manual-only)
$payment_link     = null;
$payment_provider = 'Manual';

// Flag from generate_invoice.php: 1 = manual only, do NOT create Stripe payment link
$skip_stripe = isset($_POST['skip_stripe']) && $_POST['skip_stripe'] === '1';

try {
    if ($skip_stripe) {
        // Manual-only invoice
        $payment_link     = null;
        $payment_provider = 'Manual';
        error_log("ℹ️ save_invoice.php: Invoice {$invoiceNumber} MANUAL ONLY (skip_stripe=1).");

    } elseif (get_setting('test_mode') === 'on') {
        // Test mode: fake checkout link
        $payment_link     = BASE_URL . "fake-checkout.php?invoice=" . $invoice_id;
        $payment_provider = 'Test';

    } else {
        // Live Stripe mode
        $stripeSecret = get_setting('stripe_secret');
        $stripePublic = get_setting('stripe_publishable');

        // ✅ If Stripe SDK isn't installed, don't fatal — just skip Stripe
        $stripeSdkOk = class_exists(\Stripe\Stripe::class) && class_exists(\Stripe\Checkout\Session::class);

        if ($stripeSecret && $stripePublic && $stripeSdkOk) {
            \Stripe\Stripe::setApiKey($stripeSecret);

            $stripeCurrency  = strtolower($currency_code);
            $stripeSupported = ['cad', 'usd', 'eur', 'gbp', 'aud', 'aed', 'sar'];

            $STRIPE_MAX_TOTAL = 999999.99;

            if (!in_array($stripeCurrency, $stripeSupported, true)) {
                error_log("⚠️ Currency {$currency_code} not supported by Stripe. Skipping Stripe for invoice {$invoiceNumber}.");
                $payment_link     = null;
                $payment_provider = 'Manual';

            } elseif ($invoice_total > $STRIPE_MAX_TOTAL) {
                error_log("⚠️ Invoice {$invoiceNumber} total {$invoice_total} exceeds Stripe max {$STRIPE_MAX_TOTAL}. Skipping Stripe.");
                $payment_link     = null;
                $payment_provider = 'Manual';

            } else {
                $unitAmount = (int) round($invoice_total * 100);

                error_log("💳 Stripe debug – invoice_id={$invoice_id}, invoice_number={$invoiceNumber}, invoice_total={$invoice_total}, unit_amount={$unitAmount}");

                try {
                    $session = \Stripe\Checkout\Session::create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency'     => $stripeCurrency,
                                'unit_amount'  => $unitAmount,
                                'product_data' => [
                                    'name' => 'Invoice #' . $invoiceNumber
                                ]
                            ],
                            'quantity' => 1,
                        ]],
                        'mode'        => 'payment',
                        'success_url' => BASE_URL . 'payment-success.php?invoice=' . urlencode($invoice_id),
                        'cancel_url'  => BASE_URL . 'view-invoice.php?invoice=' . urlencode($invoice_id),
                        'metadata'    => [
                            'invoice_id' => $invoice_id
                        ]
                    ]);

                    $payment_link     = $session->url;
                    $payment_provider = 'Stripe';

                } catch (Exception $e) {
                    error_log("❌ Stripe Error for invoice {$invoice_id}: " . $e->getMessage());
                    $payment_link     = null;
                    $payment_provider = 'Manual';
                }
            }

        } else {
            if (!$stripeSdkOk) {
                error_log("⚠️ Stripe SDK not installed (vendor/autoload.php missing or Stripe package not present). Skipping Stripe for invoice {$invoice_id}.");
            } else {
                error_log("⚠️ Stripe keys missing or incomplete; skipping Stripe for invoice {$invoice_id}.");
            }
            $payment_link     = null;
            $payment_provider = 'Manual';
        }
    }

    // ✅ Persist result once (no more branching updates)
    $update = $pdo->prepare("UPDATE invoices SET payment_link = ?, payment_provider = ? WHERE id = ?");
    $update->execute([$payment_link, $payment_provider, $invoice_id]);

} catch (Exception $e) {
    error_log("❌ Payment link block failed: " . $e->getMessage());
    // Never block invoice creation if payment-link logic fails
    $update = $pdo->prepare("UPDATE invoices SET payment_link = NULL, payment_provider = 'Manual' WHERE id = ?");
    $update->execute([$invoice_id]);
    $payment_link = null;
}

// Generate PDF-ready HTML
ob_start();
$invoice_data = [
    'invoice_number'  => $invoiceNumber,
    'date'            => $date,
    'due_date'        => $due_date,
    'payment_link'    => $payment_link,
    'status'          => $status,
    'items'           => $data['items'] ?? [],
    'invoice_total'   => $invoice_total,
    'currency_code'    => $currency_code,
    'currency_display' => $currency_display,
    'invoice_title_bg'   => $invoice_title_bg,
    'invoice_title_text' => $invoice_title_text,
    'is_recurring'    => $is_recurring,
    'recurrence_type' => $recurrence_type,
    'next_run_date'   => $next_run_date,
    'data'            => $data,
    'invoice_html'    => ($invoiceHtmlForDb ?? ''),
    'payment_method'  => $data['payment_method'] ?? null,

    // 🏦 Banking details for this invoice
    'bank_account_name'    => $bank_account_name,
    'bank_name'            => $bank_name,
    'bank_account_number'  => $bank_account_number,
    'bank_iban'            => $bank_iban,
    'bank_swift'           => $bank_swift,
    'bank_routing'         => $bank_routing,
    'bank_additional_info' => $bank_additional_info,
];

extract($invoice_data);
include 'template_invoice.php';
$html = ob_get_clean();

// Save files with proper names
$pdfPath  = $invoiceDir . "/{$invoiceNumber}.pdf";
$htmlPath = $invoiceDir . "/{$invoiceNumber}.html";

file_put_contents($htmlPath, $html);

// Generate PDF only if DomPDF is available
if (class_exists(\Dompdf\Dompdf::class)) {
    $dompdf = new Dompdf();

    // ✅ Better to set options before loadHtml/render
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isRemoteEnabled', true);
    
    $dompdf->loadHtml($html);
    
    // ✅ A4 Landscape
    $dompdf->setPaper('A4', 'landscape');
    
    $dompdf->render();
    file_put_contents($pdfPath, $dompdf->output());
    
} else {
    error_log('DomPDF class not available, skipping PDF generation.');
}

// Send email
if ($can_email_invoice && filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
    try {
        $company_name  = get_setting('company_name');
        $subject = "Your Invoice {$invoiceNumber} from {$company_name}";
        $pdf_path = __DIR__ . "/{$pdfPath}";

        $replacements = [
            '{{client_name}}'    => $client_name,
            '{{invoice_number}}' => $invoiceNumber,
            '{{company_name}}'   => $company_name,
            '{{total_amount}}'   => $currency_display . ' ' . number_format($invoice_total, 2),
            '{{payment_link}}'   => $payment_link ?: '#',
            '{{due_date}}'       => $due_date
        ];

        $ccList  = [];
        $bccList = [];
        
        $body = getEmailTemplateBody($pdo, 'invoice_available', $replacements, $ccList, $bccList) ?: "
            <p>Dear <strong>{$client_name}</strong>,</p>
            <p>Your invoice <strong>{$invoiceNumber}</strong> has been generated by <strong>{$company_name}</strong>.</p>
            <p><strong>Total Due:</strong> " . htmlspecialchars($currency_display) . " " . number_format($invoice_total, 2) . "</p>
            <p>Please find the attached invoice in PDF format.</p>
        ";

        sendInvoiceEmail($client_email, $client_name, $subject, $body, $pdf_path, basename($pdf_path), $ccList, $bccList);
    } catch (Exception $e) {
        error_log("❌ Email sending failed: " . $e->getMessage());
    }
} else {
    error_log("❌ Email was skipped — permission 'email_invoice' is disabled or invalid email.");
}

unset($_SESSION['invoice_data']);

// Clear any buffered output before redirect so headers work
if (ob_get_level()) {
    ob_end_clean();
}

header("Location: history.php");
exit;

?>