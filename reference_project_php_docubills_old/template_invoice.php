<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Compatibility: when regenerated from update_status.php, you may have $invoice_data but not $invoice
if (!isset($invoice) && isset($invoice_data) && is_array($invoice_data)) {
    $invoice = $invoice_data;
}

// Pull all company settings
$company_name    = get_setting('company_name');
$company_address = get_setting('company_address');
$company_phone   = get_setting('company_phone');
$company_email   = get_setting('company_email');
$company_logo_url = trim(get_setting('company_logo_url', ''));
// Dompdf demands an absolute *http/https* URL or a data-URI
if ($company_logo_url !== '') {

    // 1) Already absolute?  great – just use it.
    if (preg_match('#^https?://#i', $company_logo_url)) {
        $logo_src = $company_logo_url;

    // 2) Otherwise build an absolute URL from the site root
    } else {
        // define this **once** in config.php →  define('BASE_URL', 'https://womenfirst.ca/accounting/ig/');
        $logo_src = rtrim(BASE_URL, '/').'/'.ltrim($company_logo_url, '/');
    }

} else {
    $logo_src = null;   // nothing configured
}

// Optional debug
file_put_contents('debug_logo_path.txt', "company_logo_url: {$company_logo_url}\n", FILE_APPEND);
/*file_put_contents('debug_logo_path.txt', "Exists: " . (file_exists($local_path) ? 'YES' : 'NO') . "\n", FILE_APPEND);*/

$gst_number      = get_setting('gst_number');

// ─────────────────────────────────────────────
// ✅ Invoice Title Bar Color (PDF Heading)
// ─────────────────────────────────────────────
$allowedTitleBarColors  = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];
$allowedTitleTextColors = ['#0033D9', '#FFFFFF'];

// ✅ IMPORTANT: preserve any value already passed via extract() (save_invoice.php / update_status.php)
$passed_bg   = isset($invoice_title_bg) ? trim((string)$invoice_title_bg) : '';
$passed_text = isset($invoice_title_text) ? trim((string)$invoice_title_text) : '';

// Background (priority: passed var -> POST -> DB -> $data -> session -> default)
$invoice_title_bg = strtoupper(trim((string)(
    ($passed_bg !== '' ? $passed_bg : null)
    ?? ($_POST['invoice_title_bg'] ?? null)
    ?? ($invoice['invoice_title_bg'] ?? null)
    ?? ($data['invoice_title_bg'] ?? null)
    ?? ($_SESSION['invoice_data']['invoice_title_bg'] ?? null)
    ?? '#FFDC00'
)));

if (!in_array($invoice_title_bg, $allowedTitleBarColors, true)) {
    $invoice_title_bg = '#FFDC00';
}

// Text color (rule-based; allow passed/post but still enforce your rule)
$invoice_title_text = strtoupper(trim((string)(
    ($passed_text !== '' ? $passed_text : null)
    ?? ($_POST['invoice_title_text'] ?? null)
    ?? ($invoice['invoice_title_text'] ?? null)
    ?? ($data['invoice_title_text'] ?? null)
    ?? ($_SESSION['invoice_data']['invoice_title_text'] ?? null)
    ?? ''
)));

if (!in_array($invoice_title_text, $allowedTitleTextColors, true)) {
    $invoice_title_text = ($invoice_title_bg === '#FFDC00') ? '#0033D9' : '#FFFFFF';
}

// ✅ Always enforce rule for consistency
$invoice_title_text = ($invoice_title_bg === '#FFDC00') ? '#0033D9' : '#FFFFFF';

// ─────────────────────────────────────────────
// ✅ Strong currency fallback (handles empty strings properly)
$currency_code = strtoupper(trim((string)($currency_code ?? '')));
if ($currency_code === '' && !empty($invoice['currency_code'])) {
    $currency_code = strtoupper(trim((string)$invoice['currency_code']));
}
if ($currency_code === '') {
    $currency_code = strtoupper(trim((string)get_setting('currency_code', 'CAD')));
}

$currency_display = trim((string)($currency_display ?? ''));
if ($currency_display === '' && !empty($invoice['currency_display'])) {
    $currency_display = trim((string)$invoice['currency_display']);
}

$currency_prefix_map = [
    'CAD' => 'CA$',
    'USD' => 'US$',
    'AUD' => 'A$',
    'GBP' => '£',
    'EUR' => '€',
    'PKR' => '₨',
    'SAR' => '﷼',
    'AED' => 'د.إ'
];

if ($currency_display === '') {
    $currency_display = $currency_prefix_map[$currency_code] ?? get_setting('currency_symbol', '$');
}

// ---------------- Banking details (defaults + per-invoice overrides) ----------------

// Defaults from Settings → Payment Methods
$default_bank_account_name    = trim(get_setting('bank_account_name'));
$default_bank_name            = trim(get_setting('bank_name'));
$default_bank_account_number  = trim(get_setting('bank_account_number'));
$default_bank_iban            = trim(get_setting('bank_iban'));
$default_bank_swift           = trim(get_setting('bank_swift'));
$default_bank_routing         = trim(get_setting('bank_routing'));
$default_bank_additional_info = trim(get_setting('bank_additional_info'));

// Start with defaults
$invoice_bank_account_name    = $default_bank_account_name;
$invoice_bank_name            = $default_bank_name;
$invoice_bank_account_number  = $default_bank_account_number;
$invoice_bank_iban            = $default_bank_iban;
$invoice_bank_swift           = $default_bank_swift;
$invoice_bank_routing         = $default_bank_routing;
$invoice_bank_additional_info = $default_bank_additional_info;

// If we have an invoice row from the DB, let it override defaults
if (isset($invoice) && is_array($invoice)) {
    if (!empty($invoice['bank_account_name'])) {
        $invoice_bank_account_name = $invoice['bank_account_name'];
    }
    if (!empty($invoice['bank_name'])) {
        $invoice_bank_name = $invoice['bank_name'];
    }
    if (!empty($invoice['bank_account_number'])) {
        $invoice_bank_account_number = $invoice['bank_account_number'];
    }
    if (!empty($invoice['bank_iban'])) {
        $invoice_bank_iban = $invoice['bank_iban'];
    }
    if (!empty($invoice['bank_swift'])) {
        $invoice_bank_swift = $invoice['bank_swift'];
    }
    if (!empty($invoice['bank_routing'])) {
        $invoice_bank_routing = $invoice['bank_routing'];
    }
    if (!empty($invoice['bank_additional_info'])) {
        $invoice_bank_additional_info = $invoice['bank_additional_info'];
    }
}

// If template is being rendered directly from preview/session, let $data override DB/defaults
if (!empty($data['bank_account_name'] ?? null)) {
    $invoice_bank_account_name = $data['bank_account_name'];
}
if (!empty($data['bank_name'] ?? null)) {
    $invoice_bank_name = $data['bank_name'];
}
if (!empty($data['bank_account_number'] ?? null)) {
    $invoice_bank_account_number = $data['bank_account_number'];
}
if (!empty($data['bank_iban'] ?? null)) {
    $invoice_bank_iban = $data['bank_iban'];
}
if (!empty($data['bank_swift'] ?? null)) {
    $invoice_bank_swift = $data['bank_swift'];
}
if (!empty($data['bank_routing'] ?? null)) {
    $invoice_bank_routing = $data['bank_routing'];
}
if (!empty($data['bank_additional_info'] ?? null)) {
    $invoice_bank_additional_info = $data['bank_additional_info'];
}

$hasBankDetails =
    $invoice_bank_account_name ||
    $invoice_bank_name ||
    $invoice_bank_account_number ||
    $invoice_bank_iban ||
    $invoice_bank_swift ||
    $invoice_bank_routing ||
    $invoice_bank_additional_info;

// Should we actually display the Banking Details box?
// 1) New invoices: value comes from the hidden field in generate_invoice.php (POST)
// 2) Regenerated invoices: value comes from invoices.show_bank_details (DB)
// 3) Legacy invoices: default to 1 (show) so old PDFs keep their behaviour.
$show_bank_details = 1; // legacy default

if (isset($_POST['show_bank_details'])) {
    // Directly from the preview form
    $show_bank_details = (int) $_POST['show_bank_details'];
} elseif (isset($invoice['show_bank_details'])) {
    // Saved on the invoice row (once we store it in save_invoice.php)
    $show_bank_details = (int) $invoice['show_bank_details'];
} elseif (isset($data['show_bank_details'])) {
    // Fallback if something passed via $data
    $show_bank_details = (int) $data['show_bank_details'];
}

require_once 'middleware.php';   // make sure this is first

// Always define the variable BEFORE it’s used
$can_show_invoice_time = function_exists('has_permission')
    ? has_permission('show_invoice_time')
    : false;

/*
 | 1) If $date wasn’t passed in, look for it in common arrays.
 | 2) Then format it, showing or hiding the time.
*/
if (empty($date)) {
    $raw_invoice_date =
          $data['invoice_date']
      ?? ($invoice['invoice_date'] ?? null)
      ?? ($_SESSION['invoice_data']['invoice_date'] ?? null);

    $date = $raw_invoice_date;   // hand it back
}

if (!empty($date)) {
    $timestamp = strtotime($date);

    if (!$can_show_invoice_time) {
        $date = date('Y-m-d', $timestamp); // Only date
    } else {
        // ✅ show time only if not exactly midnight
        if (date('H:i', $timestamp) === '00:00') {
            $date = date('Y-m-d', $timestamp);
        } else {
            $date = date('Y-m-d h:i A', $timestamp);
        }
    }
}
// ── END new invoice-date logic ───────────────────────────────


/*------------------------------------------------------------
| Dynamically pick the colour for all amounts
|------------------------------------------------------------*/
$status_val  = strtolower(trim($status ?? $invoice['status'] ?? 'unpaid'));
$amountColor = ($status_val === 'paid') ? '#28a745' : '#dc3545'; // green vs red

/*------------------------------------------------------------
| Decide which payment method to SHOW on the PDF
| (hide Stripe-based methods from display)
|------------------------------------------------------------*/
$raw_payment_method = null;

// Prefer explicit $payment_method if provided
if (isset($payment_method) && $payment_method !== '') {
    $raw_payment_method = $payment_method;
} elseif (!empty($invoice['payment_method'] ?? null)) {
    // Fallback to DB field if available
    $raw_payment_method = $invoice['payment_method'];
}

$display_payment_method = null;
if (!empty($raw_payment_method)) {
    $method = trim($raw_payment_method);

    // ❌ Do NOT show Stripe-based methods on the invoice PDF
    // (they stay in DB for logs, but not printed)
    if (stripos($method, 'stripe') === false) {
        $display_payment_method = $method;
    }
}

/* ----------------------------------------------------------
 | Restore full Bill-To when the invoice is opened from DB
 | (it will already be present when you’re creating a brand-new
 | invoice in the same request, so we check first)
 * --------------------------------------------------------- */
if (empty($data['bill_to']) && !empty($invoice['bill_to_json'])) {
    $decoded = json_decode($invoice['bill_to_json'], true);
    if (is_array($decoded)) {
        $data['bill_to'] = $decoded;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Invoice <?= htmlspecialchars($invoice_number) ?></title>
  <?php
  // --- PDF Font Fix (Unicode currencies like ₨, ﷼, د.إ) ---
  // Optional custom fonts (recommended for SAR/AED). Create /fonts/ next to this file.
  $fontNotoSansPath   = __DIR__ . '/fonts/NotoSans-Regular.ttf';
  $fontNotoArabicPath = __DIR__ . '/fonts/NotoNaskhArabic-Regular.ttf';

  $notoSansSrc   = file_exists($fontNotoSansPath) ? 'file://' . realpath($fontNotoSansPath) : '';
  $notoArabicSrc = file_exists($fontNotoArabicPath) ? 'file://' . realpath($fontNotoArabicPath) : '';
  ?>
    <style>
      <?php if ($notoSansSrc): ?>
      @font-face {
        font-family: "NotoSans";
        src: url("<?= $notoSansSrc ?>") format("truetype");
        font-weight: normal;
        font-style: normal;
      }
      <?php endif; ?>
    
      <?php if ($notoArabicSrc): ?>
        @font-face {
          font-family: "NotoArabic";
          src: url("<?= $notoArabicSrc ?>") format("truetype");
          font-weight: normal;
          font-style: normal;
        }
        <?php endif; ?>
        
        /* ✅ Only currency symbols use Unicode-capable fonts (always defined) */
        .currency-glyph {
            font-family:
              <?php if ($notoArabicSrc) echo '"NotoArabic", '; ?>
              <?php if ($notoSansSrc)   echo '"NotoSans", '; ?>
              "DejaVu Sans",
              Arial,
              sans-serif !important;
        }
    </style>

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #001028;
    }

    /* ✅ PDF Page Setup */
    @page {
      size: A4 landscape;
      margin: 12mm;
    }
    
    html, body {
      width: 100%;
    }

    .container {
      width: 100%;
      max-width: 100%;
      margin: 0;
    }
    
    .logo {
      margin-bottom: 8px;
    }

    .logo img {
      height: 70px;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .header-table td {
      vertical-align: top;
      padding: 2px 6px;
      font-size: 12px;
      border: none;
    }

    .left-header {
      text-align: left;
      width: 50%;
    }

    .right-header {
      text-align: right;
      width: 50%;
    }

    .bill-to-label {
      font-weight: bold;
      font-size: 13px;
      margin-bottom: 3px; /* Reduced spacing */
    }

    h1 {
      text-align: center;
      font-size: 20px;
      color: #5D6975;
      margin: 20px 0;
      border-top: 1px solid #5D6975;
      border-bottom: 1px solid #5D6975;
      padding: 10px 0;
    }

    .invoice-titlebar {
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      margin: 20px 0 18px;
      padding: 12px 0;
      letter-spacing: 0.5px;
    }

    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      word-wrap: break-word;
    }

    .invoice-table th,
    .invoice-table td {
      padding: 4px;
      font-size: 10px;
      border: 1px solid #ccc;
      text-align: left;
      vertical-align: top;
      white-space: normal;
      word-break: break-word;
    }

    .invoice-table th {
      background-color: #f0f0f0;
    }

    .total-row td {
      font-weight: bold;
      border-top: 2px solid #5D6975;
      background: #f9f9f9;
      font-size: 13px;
    }

    .total-row td {
      font-weight: bold;
      font-size: 13px;
      background: #f9f9f9;
      border-top: 2px solid #5D6975;
    }
    
    .total-row td:nth-child(6) {
      text-align: left;
      border-left: none;
      border-right: none;
    }

    .total-row td:last-child {
      text-align: right;
    }
    
    .total-row {
      margin-top: 15px;
    }

    .pay-button {
      display: inline-block;
      background: #28a745;
      color: white;
      text-decoration: none;
      padding: 10px 18px;
      border-radius: 4px;
      margin-top: 20px;
      font-weight: bold;
      font-size: 13px;
    }

    .notice-box {
      margin-top: 30px;
      padding: 10px 15px;
      border-left: 4px solid <?= htmlspecialchars($invoice_title_bg) ?>;
      background: #f5f5f5;
      font-size: 12px;
    }

    footer {
      text-align: center;
      font-size: 11px;
      color: #5D6975;
      border-top: 1px solid #C1CED9;
      padding-top: 10px;
      margin-top: 40px;
    }
    
    img {
      max-width: 100%;
      height: auto;
    }

  </style>
</head>
<body>
    <?php if ($status_val === 'paid'): ?>
  <div style="position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; color: rgba(0, 128, 0, 0.15); font-weight: bold; z-index: 999;">
    PAID
  </div>
<?php endif; ?>
  <div class="container">
    <!-- HEADER (logo + company vs Bill-To) -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:30px;">
      <!-- 1st row – logo  |  Bill-To label -->
      <tr>
        <td style="width:50%; text-align:left; vertical-align:top;">
          <?php if ($logo_src): ?>
            <img src="<?= htmlspecialchars($logo_src) ?>" alt="Logo" style="height:70px;">
          <?php endif; ?>
        </td>
        <td style="width:50%; text-align:right; vertical-align:top;">
          <div style="font-weight:bold; font-size:13px;">Bill To:</div>
        </td>
      </tr>
    
      <!-- 2nd row – company lines  |  client lines -->
      <tr>
        <!-- company -->
        <td style="text-align:left; vertical-align:top; font-size:12px; line-height:1.4; padding-top:6px;">
          <strong><?= htmlspecialchars($company_name) ?></strong><br>
          <?= nl2br(htmlspecialchars($company_address)) ?><br>
          <?= htmlspecialchars($company_phone) ?><br>
          <?= htmlspecialchars($company_email) ?><br>
          GST/HST: <?= htmlspecialchars($gst_number) ?>
        </td>
    
        <!-- client -->
        <td style="text-align:right; vertical-align:top; font-size:12px; line-height:1.4; padding-top:6px;">
          <?php foreach ($data['bill_to'] as $v): ?>
            <?php if (!empty($v)): ?>
              <?= htmlspecialchars($v) ?><br>
            <?php endif; ?>
          <?php endforeach; ?>
        </td>
      </tr>
    </table>

    <!-- INVOICE TITLE -->
    <div class="invoice-titlebar"
         style="background: <?= htmlspecialchars($invoice_title_bg) ?>; color: <?= htmlspecialchars($invoice_title_text) ?>;">
      INVOICE
    </div>
    
    <!-- DATE ROW (keeps Invoice Date + Due Date perfectly aligned on same row) -->
    <table style="width:100%; margin-bottom:20px; border-collapse:collapse; table-layout:fixed;">
      <tr>
        <td style="width:50%; text-align:left; font-size:12px; vertical-align:top; line-height:1.4;">
          <strong>Invoice Date:</strong> <?= !empty($date) ? htmlspecialchars($date, ENT_QUOTES, 'UTF-8') : '' ?>
    
          <?php if (!empty($invoice_number)): ?>
            <br><strong>Invoice #:</strong> <?= htmlspecialchars($invoice_number, ENT_QUOTES, 'UTF-8') ?>
          <?php endif; ?>
        </td>
    
        <td style="width:50%; text-align:right; font-size:12px; vertical-align:top; line-height:1.4;">
          <strong>Due Date:</strong>
          <?php if (!empty($due_date) && strtotime($due_date)): ?>
            <?= date('Y-m-d', strtotime($due_date)) ?>
            <?php
              $time = date('H:i', strtotime($due_date));
              if ($time !== '00:00') {
                  echo ' ' . date('g:i A', strtotime($due_date));
              }
            ?>
          <?php else: ?>
            N/A
          <?php endif; ?>
        </td>
      </tr>
    </table>
    
  <!-- Invoice Items Table -->
<?php
// 🔁 Make sure $invoice_html is loaded from DB/session if not explicitly passed
if (empty($invoice_html)) {
    if (!empty($invoice['html'])) {
        $invoice_html = $invoice['html'];
    } elseif (!empty($data['invoice_html'])) {
        $invoice_html = $data['invoice_html'];
    }
}
?>

<?php
// ✅ 1) Make sure payment_link exists when rendering from DB/regeneration
if (empty($payment_link)) {
    $payment_link =
        $invoice['payment_link']         ??  // preferred if you have it
        $invoice['stripe_payment_link']  ??  // common alt name
        $invoice['checkout_url']         ??  // common alt name
        $invoice['pay_now_url']          ??  // common alt name
        '';
}
$payment_link = trim((string)$payment_link);

// ✅ 2) If invoice_html accidentally contains a FULL HTML document (saved by update_status.php), extract body only
if (!empty($invoice_html) && stripos($invoice_html, '<html') !== false) {
    if (preg_match('~<body[^>]*>(.*)</body>~is', $invoice_html, $m)) {
        $invoice_html = $m[1];
    }
}

// ✅ 3) Remove any <footer> that may exist inside saved html (we output footer ourselves at bottom)
if (!empty($invoice_html)) {
    $invoice_html = preg_replace('~<footer\b[^>]*>.*?</footer>~is', '', $invoice_html);
}

if (!function_exists('strip_tax_columns_from_invoice_html')) {
function strip_tax_columns_from_invoice_html($html) {
    if (!is_string($html) || trim($html) === '') {
        return $html;
    }
    if (!class_exists('DOMDocument')) {
        return $html;
    }
    if (!preg_match('/Tax\\s*(0*\\d+\\s*\\(|\\()/i', $html)) {
        return $html;
    }

    $dom = new DOMDocument();
    $prev = libxml_use_internal_errors(true);

    $flags = 0;
    if (defined('LIBXML_HTML_NOIMPLIED')) {
        $flags |= LIBXML_HTML_NOIMPLIED;
    }
    if (defined('LIBXML_HTML_NODEFDTD')) {
        $flags |= LIBXML_HTML_NODEFDTD;
    }

    $wrapper_id = 'invoice-html-wrapper';
    $dom->loadHTML('<div id="' . $wrapper_id . '">' . $html . '</div>', $flags);

    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $xpath = new DOMXPath($dom);
    $tables = $xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " invoice-table ")]');
    if ($tables->length === 0) {
        $tables = $xpath->query('//table');
    }

    foreach ($tables as $table) {
        $headerRow = $xpath->query('.//thead/tr[1]', $table)->item(0);
        if (!$headerRow) {
            $headerRow = $xpath->query('.//tr[1]', $table)->item(0);
        }
        if (!$headerRow) {
            continue;
        }

        $headerCells = $xpath->query('./th', $headerRow);
        if ($headerCells->length === 0) {
            $headerCells = $xpath->query('./td', $headerRow);
        }
        if ($headerCells->length === 0) {
            continue;
        }

        $removeIdx = [];
        for ($i = 0; $i < $headerCells->length; $i++) {
            $label = trim(preg_replace('/\s+/', ' ', $headerCells->item($i)->textContent));
            if ($label === '') {
                continue;
            }
            if (preg_match('/^Tax\\b.*%/i', $label) || preg_match('/^Tax\\s*0*\\d+/i', $label)) {
                $removeIdx[] = $i;
            }
        }

        if (!$removeIdx) {
            continue;
        }

        rsort($removeIdx);

        foreach ($removeIdx as $idx) {
            $cell = $headerCells->item($idx);
            if ($cell && $cell->parentNode) {
                $cell->parentNode->removeChild($cell);
            }
        }

        $rows = $xpath->query('.//tbody/tr', $table);
        if ($rows->length === 0) {
            $rows = $xpath->query('.//tr', $table);
        }

        foreach ($rows as $row) {
            if ($row->isSameNode($headerRow)) {
                continue;
            }
            $cells = $xpath->query('./td', $row);
            if ($cells->length === 0) {
                $cells = $xpath->query('./th', $row);
            }
            foreach ($removeIdx as $idx) {
                $cell = $cells->item($idx);
                if ($cell && $cell->parentNode) {
                    $cell->parentNode->removeChild($cell);
                }
            }
        }
    }

    $wrapper = $dom->getElementById($wrapper_id);
    if ($wrapper) {
        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }
        return $result;
    }

    return $dom->saveHTML();
}
}

if (!function_exists('parse_money_value')) {
function parse_money_value($value) {
    if (!is_string($value)) {
        $value = (string)$value;
    }
    $num = preg_replace('/[^0-9.]/', '', $value);
    if ($num === '' || !is_numeric($num)) {
        return 0.0;
    }
    return (float)$num;
}
}

if (!function_exists('upgrade_invoice_html_line_items')) {
function upgrade_invoice_html_line_items($html, $currency_display) {
    if (!is_string($html) || trim($html) === '') {
        return $html;
    }
    if (stripos($html, 'Line Item Amount (Net)') !== false) {
        return $html;
    }
    if (!class_exists('DOMDocument')) {
        return $html;
    }

    $dom = new DOMDocument();
    $prev = libxml_use_internal_errors(true);

    $flags = 0;
    if (defined('LIBXML_HTML_NOIMPLIED')) {
        $flags |= LIBXML_HTML_NOIMPLIED;
    }
    if (defined('LIBXML_HTML_NODEFDTD')) {
        $flags |= LIBXML_HTML_NODEFDTD;
    }

    $wrapper_id = 'invoice-html-upgrade';
    $dom->loadHTML('<div id="' . $wrapper_id . '">' . $html . '</div>', $flags);

    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $xpath = new DOMXPath($dom);
    $tables = $xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " invoice-table ")]');
    if ($tables->length === 0) {
        $tables = $xpath->query('//table');
    }

    foreach ($tables as $table) {
        $headerRow = $xpath->query('.//thead/tr[1]', $table)->item(0);
        if (!$headerRow) {
            $headerRow = $xpath->query('.//tr[1]', $table)->item(0);
        }
        if (!$headerRow) {
            continue;
        }

        $headerCells = $xpath->query('./th', $headerRow);
        if ($headerCells->length === 0) {
            $headerCells = $xpath->query('./td', $headerRow);
        }
        if ($headerCells->length === 0) {
            continue;
        }

        $baseIdx = null;
        $bestScore = -1;
        for ($i = 0; $i < $headerCells->length; $i++) {
            $label = trim(preg_replace('/\s+/', ' ', $headerCells->item($i)->textContent));
            $labelLower = strtolower($label);
            if ($labelLower === '') {
                continue;
            }

            $score = 0;
            if (strpos($labelLower, 'total value') !== false || strpos($labelLower, 'total amount') !== false) {
                $score = 4;
            } elseif (strpos($labelLower, 'amount paid') !== false) {
                $score = 4;
            } elseif (strpos($labelLower, 'amount') !== false && strpos($labelLower, 'unit') === false) {
                $score = 3;
            } elseif (strpos($labelLower, 'total') !== false) {
                $score = 2;
            } elseif (strpos($labelLower, 'value') !== false && strpos($labelLower, 'unit') === false) {
                $score = 1;
            }

            if ($score >= $bestScore) {
                $bestScore = $score;
                $baseIdx = $i;
            }
        }

        if ($baseIdx === null) {
            continue;
        }

        $rows = $xpath->query('.//tbody/tr', $table);
        if ($rows->length === 0) {
            $rows = $xpath->query('.//tr', $table);
        }

        foreach ($rows as $row) {
            if ($row->isSameNode($headerRow)) {
                continue;
            }
            $cells = $xpath->query('./td', $row);
            if ($cells->length === 0 || $baseIdx >= $cells->length) {
                continue;
            }

            $cell = $cells->item($baseIdx);
            if (!$cell) {
                continue;
            }

            $cellHtml = '';
            foreach ($cell->childNodes as $child) {
                $cellHtml .= $dom->saveHTML($child);
            }
            $cellHtml = preg_replace('/<br\\s*\\/?>/i', "\n", $cellHtml);
            $cellText = trim(html_entity_decode(strip_tags($cellHtml), ENT_QUOTES | ENT_HTML5));
            if ($cellText === '') {
                continue;
            }

            $lines = array_values(array_filter(array_map('trim', preg_split('/\\n+/', $cellText))));
            if (!$lines) {
                continue;
            }

            $baseLine = array_shift($lines);
            $baseAmount = parse_money_value($baseLine);
            $taxLabels = [];
            $taxTotal = 0.0;

            foreach ($lines as $line) {
                if ($line === '') {
                    continue;
                }
                $label = $line;
                $amount = 0.0;
                if (strpos($line, ':') !== false) {
                    $parts = explode(':', $line, 2);
                    $label = trim($parts[0]);
                    $amount = parse_money_value($parts[1] ?? '');
                }
                if ($label !== '') {
                    $taxLabels[] = $label;
                }
                $taxTotal += $amount;
            }

            $lineTotal = $baseAmount + $taxTotal;
            $newLines = [];
            $newLines[] = ['Line Item Amount (Net):', $currency_display . number_format($baseAmount, 2)];
            if ($taxLabels) {
                $newLines[] = ['Applied Tax(es):', implode(', ', $taxLabels)];
                $newLines[] = ['Line Item Total:', $currency_display . number_format($lineTotal, 2)];
            }

            while ($cell->firstChild) {
                $cell->removeChild($cell->firstChild);
            }

            foreach ($newLines as $idx => $line) {
                $div = $dom->createElement('div');
                if ($idx + 1 < count($newLines)) {
                    $div->setAttribute('style', 'margin-bottom:2px;');
                }
                $strong = $dom->createElement('strong');
                $strong->appendChild($dom->createTextNode($line[0] . ' '));
                $div->appendChild($strong);
                $div->appendChild($dom->createTextNode($line[1]));
                $cell->appendChild($div);
            }
        }
    }

    $wrapper = $dom->getElementById($wrapper_id);
    if ($wrapper) {
        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }
        return $result;
    }

    return $dom->saveHTML();
}
}

if (!function_exists('build_invoice_totals_html')) {
function build_invoice_totals_html($summary, $currency_display) {
    if (!is_array($summary)) {
        return '';
    }

    $money = function ($value) use ($currency_display) {
        return $currency_display . number_format((float)$value, 2);
    };

    $rows = [];
    $rows[] = '<tr>'
        . '<th colspan="2" style="text-align:left;padding:7px 8px;background:#f5f6f8;border-bottom:1px solid #e5e7eb;font-size:12px;letter-spacing:0.4px;">'
        . 'Invoice Totals'
        . '</th>'
        . '</tr>';

    $addRow = function ($label, $amount, $emphasis = false) use (&$rows, $money) {
        $weight = $emphasis ? 'font-weight:700;' : '';
        $bg = $emphasis ? 'background:#f9fafb;' : '';
        $rows[] = '<tr>'
            . '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;' . $weight . $bg . '">' . htmlspecialchars($label) . '</td>'
            . '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;' . $weight . $bg . '">' . htmlspecialchars($money($amount)) . '</td>'
            . '</tr>';
    };

    $addRow('Net Total', $summary['netTotal'] ?? 0);
    $addRow('Total Line-Level Taxes', $summary['lineTaxTotal'] ?? 0);
    $addRow('Subtotal', $summary['subtotal'] ?? 0);

    if (!empty($summary['invoiceSubtotalTaxes']) && is_array($summary['invoiceSubtotalTaxes'])) {
        foreach ($summary['invoiceSubtotalTaxes'] as $tax) {
            $name = (string)($tax['name'] ?? '');
            $rateLabel = (string)($tax['rateLabel'] ?? '0');
            $addRow($name . ' (' . $rateLabel . '% on Subtotal)', $tax['amount'] ?? 0);
        }
    }

    $showAdjusted = !empty($summary['invoiceSubtotalTaxes']) || !empty($summary['invoiceAdjustedTaxes']);
    if ($showAdjusted) {
        $addRow('Adjusted Subtotal', $summary['adjustedSubtotal'] ?? 0);
    }

    if (!empty($summary['invoiceAdjustedTaxes']) && is_array($summary['invoiceAdjustedTaxes'])) {
        foreach ($summary['invoiceAdjustedTaxes'] as $tax) {
            $name = (string)($tax['name'] ?? '');
            $rateLabel = (string)($tax['rateLabel'] ?? '0');
            $addRow($name . ' (' . $rateLabel . '% on Adjusted Subtotal)', $tax['amount'] ?? 0);
        }
    }

    $addRow('Grand Total', $summary['grandTotal'] ?? 0, true);

    return '<table class="invoice-totals" data-invoice-totals="1" style="width:60%;max-width:420px;margin-top:16px;border-collapse:collapse;font-size:12px;border:1px solid #e5e7eb;margin-left:auto;margin-right:0;">'
        . implode('', $rows)
        . '</table>';
}
}

$invoice_tax_summary_raw = '';
if (isset($invoice_tax_summary) && $invoice_tax_summary !== '') {
    $invoice_tax_summary_raw = (string)$invoice_tax_summary;
} elseif (isset($_POST['invoice_tax_summary']) && $_POST['invoice_tax_summary'] !== '') {
    $invoice_tax_summary_raw = (string)$_POST['invoice_tax_summary'];
} elseif (!empty($data['invoice_tax_summary'])) {
    $invoice_tax_summary_raw = (string)$data['invoice_tax_summary'];
}

$invoice_tax_summary = null;
if ($invoice_tax_summary_raw !== '') {
    $decoded = json_decode($invoice_tax_summary_raw, true);
    if (is_array($decoded)) {
        $invoice_tax_summary = $decoded;
    }
}
?>

<?php if (!empty($invoice_html) && strip_tags(trim($invoice_html)) !== ''): ?>

<?php
// ✅ Remove any old Total/Pay blocks from saved HTML so it can't appear after footer
$invoice_html = preg_replace('~<div\s+style="margin-top:20px;?\s*text-align:right;?\s*font-size:16px;?\s*font-weight:bold;?">\s*.*?\s*</div>~is', '', $invoice_html);

// Also remove the injected 2-column table version if it exists
$invoice_html = preg_replace('~<table\s+style="width:100%;\s*margin-top:20px;?">\s*<tr>.*?</tr>\s*</table>~is', '', $invoice_html);

// Remove any saved Total Amount blocks to prevent duplicates in the PDF
$invoice_html = preg_replace('~<div\b[^>]*>\s*(?:<strong>\s*)?Total Amount:\s*(?:</strong>\s*)?.*?</div>~is', '', $invoice_html);


// ✅ Color-inject currency amounts
// Remove legacy Total Taxes blocks from saved HTML
$invoice_html = preg_replace('~<div\b[^>]*>\s*(?:<strong>\s*)?Total Taxes:\s*.*?</div>~is', '', $invoice_html);

// Upgrade line-item display if it lacks net/tax/total labels
$invoice_html = upgrade_invoice_html_line_items($invoice_html, $currency_display);

// Inject totals table when missing and summary is available
if (is_string($invoice_html) && strpos($invoice_html, 'data-invoice-totals="1"') === false && is_array($invoice_tax_summary)) {
    $invoice_html .= build_invoice_totals_html($invoice_tax_summary, $currency_display);
}

$curEsc = preg_quote($currency_display, '/');
$invoice_html = preg_replace(
    '/(' . $curEsc . ')\\s*([0-9,.]+)/i',
    '<span style="color:' . $amountColor . ';"><span class="currency-glyph">$1</span>&nbsp;$2</span>',
    $invoice_html
);

$has_invoice_totals = is_string($invoice_html)
    && strpos($invoice_html, 'data-invoice-totals="1"') !== false;
?>
<?= $invoice_html ?>

<?php
// ✅ Decide status for Pay Now
$effectiveStatus = '';
if (!empty($status)) {
    $effectiveStatus = strtolower(trim($status));
} elseif (!empty($invoice['status'])) {
    $effectiveStatus = strtolower(trim($invoice['status']));
} else {
    $effectiveStatus = 'unpaid';
}

// ✅ Total amount fallback
$finalTotal = (float)($invoice_total ?? 0);
if ($finalTotal <= 0 && !empty($invoice['total_amount'])) {
    $finalTotal = (float)$invoice['total_amount'];
}
?>

<table style="width:100%; margin-top:20px;">
  <tr>
    <td style="text-align:left;">
      <?php if ($effectiveStatus === 'unpaid' && !empty($payment_link)): ?>
        <a href="<?= htmlspecialchars($payment_link) ?>"
           class="pay-button"
           style="margin:0;display:inline-block;vertical-align:middle;"
           target="_blank">Pay Now</a>
      <?php endif; ?>
    </td>

    <td style="text-align:right;font-size:16px;font-weight:bold;">
      <?php if (!$has_invoice_totals): ?>
        <strong>Grand Total:</strong>
        <span style="color: <?= $amountColor ?>;">
          <span class="currency-glyph"><?= htmlspecialchars($currency_display) ?></span>&nbsp;<?= number_format($finalTotal, 2) ?>
        </span>
      <?php endif; ?>
    </td>
  </tr>

  <?php if ($status_val === 'paid' && !empty($display_payment_method)): ?>
    <tr>
      <td colspan="2" style="text-align:right; font-size:12px; padding-top:6px;">
        <strong>Payment Method:</strong> <?= htmlspecialchars($display_payment_method) ?>
      </td>
    </tr>
  <?php endif; ?>
</table>

<?php else: ?>

  <table class="invoice-table">
    <thead>
      <tr>
        <th>Booking ID</th>
        <th>Car Type</th>
        <th>Pick-up</th>
        <th>Drop-off</th>
        <th>Booking Time</th>
        <th>Amount Paid</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['Booking ID'] ?? '') ?></td>
          <td><?= htmlspecialchars($item['Car Type'] ?? '') ?></td>
          <td><?= htmlspecialchars($item['Pick-up'] ?? '') ?></td>
          <td><?= htmlspecialchars($item['Drop-off'] ?? '') ?></td>
          <td><?= htmlspecialchars($item['Booking Time'] ?? '') ?></td>
          <td>
            <span style="color: <?= $amountColor ?>;">
              <span class="currency-glyph"><?= htmlspecialchars($currency_display) ?></span>&nbsp;<?= number_format($item['Amount Paid'] ?? 0, 2) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($item['Status'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php
    $effectiveStatus = !empty($status) ? strtolower(trim($status)) : strtolower(trim($invoice['status'] ?? 'unpaid'));
    $canShowPayNowInline = ($effectiveStatus === 'unpaid' && !empty($payment_link));
  ?>

  <table style="width: 100%; margin-top: 10px;">
    <tr class="total-row">
      <td colspan="4" style="text-align:left;">
        <?php if ($canShowPayNowInline): ?>
          <a href="<?= htmlspecialchars($payment_link) ?>"
             class="pay-button"
             style="margin:0;display:inline-block;vertical-align:middle;"
             target="_blank">Pay Now</a>
        <?php endif; ?>
      </td>

      <td colspan="4" style="text-align:right;">
        <strong>Grand Total:</strong>
        <span style="color: <?= $amountColor ?>;">
          <span class="currency-glyph"><?= htmlspecialchars($currency_display) ?></span>&nbsp;<?= number_format($invoice_total, 2) ?>
        </span>
      </td>
    </tr>

    <?php if ($status_val === 'paid' && !empty($display_payment_method)): ?>
      <tr class="total-row">
        <td colspan="8" style="text-align:right;">
          <strong>Payment Method:</strong> <?= htmlspecialchars($display_payment_method) ?>
        </td>
      </tr>
    <?php endif; ?>
  </table>
<?php endif; ?>

        <?php
      // 🔍 Debug: log if invoice is unpaid but has no payment link
      $debugStatus = '';
      if (!empty($status)) {
          $debugStatus = strtolower(trim($status));
      } elseif (!empty($invoice['status'])) {
          $debugStatus = strtolower(trim($invoice['status']));
      }

      if ($debugStatus === 'unpaid' && empty($payment_link)) {
          error_log(
              "⚠️ template_invoice.php: Unpaid invoice has no payment_link. " .
              "invoice_number={$invoice_number}, total={$invoice_total}"
          );
      }
    ?>

    <?php if ($hasBankDetails && $show_bank_details): ?>
      <div class="notice-box">
        <strong>Banking Details</strong><br>
        <?php if (!empty($invoice_bank_account_name)): ?>
          Account Name: <?= htmlspecialchars($invoice_bank_account_name) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_name)): ?>
          Bank: <?= htmlspecialchars($invoice_bank_name) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_account_number)): ?>
          Account Number: <?= htmlspecialchars($invoice_bank_account_number) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_iban)): ?>
          IBAN: <?= htmlspecialchars($invoice_bank_iban) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_swift)): ?>
          SWIFT / BIC: <?= htmlspecialchars($invoice_bank_swift) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_routing)): ?>
          Routing / Sort Code: <?= htmlspecialchars($invoice_bank_routing) ?><br>
        <?php endif; ?>
        <?php if (!empty($invoice_bank_additional_info)): ?>
          <div style="margin-top:4px;">
            <?= nl2br(htmlspecialchars($invoice_bank_additional_info)) ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($notice = get_setting('invoice_notice')): ?>
      <div class="notice-box">
        <strong>NOTICE:</strong> <?= htmlspecialchars($notice) ?>
      </div>
    <?php endif; ?>

    <footer>
      Invoice was generated electronically and is valid without signature or seal.
    </footer>
  </div>
</body>
</html>
