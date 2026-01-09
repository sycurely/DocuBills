<?php
session_start();

// 🔐 If session expired, force login before any output
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Your session has expired. Please log in again.'));
    exit;
}

$activeMenu = 'create-invoice';

require_once 'config.php';
require_once 'middleware.php';
require_once 'styles.php';
require_once 'mailer.php';

$can_edit_invoice         = has_permission('edit_invoice');
$can_save_invoice         = has_permission('save_invoice');
$can_add_field            = has_permission('add_invoice_field');
$can_show_due_date        = has_permission('show_due_date');
$can_show_due_time        = has_permission('show_due_time');
$can_show_invoice_date    = has_permission('show_invoice_date');
$can_show_invoice_time    = has_permission('show_invoice_time');
$can_show_checkboxes      = has_permission('show_invoice_checkboxes');
$can_toggle_bank_details  = has_permission('toggle_bank_details');
$can_manage_recurring     = has_permission('manage_recurring_invoices');


// Load data from session
$data = $_SESSION['invoice_data'] ?? null;
if (!$data) {
    die("No invoice data available. Please go back and re-upload.");
}

/**
 * Normalize a value from Excel into a float.
 * Handles:
 * - numbers stored as strings
 * - spaces / non-breaking spaces
 * - commas vs dots
 * - extra currency symbols / text
 */
function parseAmount($value): float {
    if ($value === null || $value === '') {
        return 0.0;
    }

    // If it's already numeric, just cast
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    $str = (string) $value;

    // Remove regular spaces + non-breaking spaces
    $str = str_replace(["\xC2\xA0", ' '], '', $str);

    // If we have both comma and dot, treat comma as thousands sep and remove it
    if (strpos($str, ',') !== false && strpos($str, '.') !== false) {
        $str = str_replace(',', '', $str);
    } else {
        // Otherwise treat comma as decimal separator
        $str = str_replace(',', '.', $str);
    }

    // Strip everything except digits, dot and minus
    $str = preg_replace('/[^0-9.\-]/', '', $str);

    return is_numeric($str) ? (float) $str : 0.0;
}

// ─────────────────────────────────────────────
// Price mode + column handling
// ─────────────────────────────────────────────

// New: configuration stored by price_select.php
$priceCfg = $_SESSION['price_config'] ?? null;

// Defaults
$manual_mode  = false;
$selected_col = null;       // column NAME, not index
$sum          = 0.0;
$includeCols  = [];

// If we arrived here via redirect from price_select.php, use that config
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $priceCfg) {
    $price_mode   = $priceCfg['price_mode']   ?? 'column';
    $selected_col = $priceCfg['price_column'] ?? null;
    $manual_mode  = ($price_mode === 'manual');

    // Use the pre-calculated total but also recalc defensively
    if (!$manual_mode && $selected_col) {
        $sum = 0.0;
        foreach ($data['items'] as $row) {
            $sum += parseAmount($row[$selected_col] ?? '');
        }
    }

    $_SESSION['invoice_data']['price_column'] = $selected_col;
    $_SESSION['invoice_data']['total']        = $sum;

    // Rebuild the visible columns list from stored indexes
    $storedIdx = $priceCfg['include_cols'] ?? [];
    $storedIdx = is_array($storedIdx) ? $storedIdx : [];
    $storedIdx = array_slice(array_map('intval', $storedIdx), 0, 15);

    foreach ($storedIdx as $i) {
        if (isset($data['headers'][$i])) {
            $includeCols[$i] = $data['headers'][$i];
        }
    }
} else {
    // Legacy / direct POST behaviour (kept for safety)
    $manual_mode  = ($_POST['price_mode'] ?? '') === 'manual';
    $selected_col = $_POST['price_column'] ?? null;

    if (!$manual_mode && $selected_col) {
        foreach ($data['items'] as $row) {
            $sum += parseAmount($row[$selected_col] ?? '');
        }
        $_SESSION['invoice_data']['price_column'] = $selected_col;
        $_SESSION['invoice_data']['total']        = $sum;
    } elseif ($manual_mode) {
        $_SESSION['invoice_data']['total'] = 0;
    }

    // Column selection from POST (original logic)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['include_cols'])) {
        $raw = array_slice(array_map('intval', $_POST['include_cols']), 0, 15);
        foreach ($raw as $i) {
            if (isset($data['headers'][$i])) {
                $includeCols[$i] = $data['headers'][$i];
            }
        }
    }
}

// Fallback: if nothing came through, show first 15 headers
if (!$includeCols) {
    foreach (array_slice($data['headers'], 0, 15) as $i => $h) {
        $includeCols[$i] = $h;
    }
}

// Work out which <td> index is the price column (for JS totals)
$priceColIndex = null;
if (!$manual_mode && $selected_col) {
    $headersForSearch = array_values($includeCols);
    $pos = array_search($selected_col, $headersForSearch, true);
    if ($pos !== false) {
        // if checkboxes are shown, the first <th> is the checkbox column
        $priceColIndex = $pos + ($can_show_checkboxes ? 1 : 0);
    }
}

// Get payment method if exists (but do not show "Stripe" on the invoice)
$final_payment_method = null;
if (!empty($data['invoice_id'])) {
    $stmt = $pdo->prepare("SELECT status, payment_method FROM invoices WHERE id = ?");
    $stmt->execute([(int)$data['invoice_id']]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inv && strtolower($inv['status']) === 'paid' && !empty($inv['payment_method'])) {
        $method = trim($inv['payment_method']);

        // Hide Stripe-based methods from the visible invoice
        // (but still keep them in the database)
        if (stripos($method, 'stripe') === false) {
            $final_payment_method = $method;
        }
    }
}

// Load company settings
$company_name    = get_setting('company_name');
$company_address = get_setting('company_address');
$company_phone   = get_setting('company_phone');
$company_email   = get_setting('company_email');
$gst_number      = get_setting('gst_number');
$company_logo    = get_setting('company_logo_url');

// ✅ Currency settings (from Settings → Basic) + per-invoice override
$default_currency_code   = strtoupper(get_setting('currency_code', 'CAD'));
$default_currency_symbol = get_setting('currency_symbol', '$');

// Allowed currencies (edit anytime)
$allowedCurrencies = [
  'CAD' => ['label' => 'CAD', 'display' => 'CA$'],
  'USD' => ['label' => 'USD', 'display' => 'US$'],
  'AUD' => ['label' => 'AUD', 'display' => 'A$'],
  'GBP' => ['label' => 'GBP', 'display' => '£'],
  'EUR' => ['label' => 'EUR', 'display' => '€'],
  'PKR' => ['label' => 'PKR', 'display' => 'PKR'],
  'SAR' => ['label' => 'SAR', 'display' => 'SAR'],
  'AED' => ['label' => 'AED', 'display' => 'AED'],
];

// ─────────────────────────────────────────────
// Invoice Title Bar Color (PDF heading section)
// ─────────────────────────────────────────────
$allowedTitleBarColors = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];

// default background + text color rule:
// ✅ Yellow (#FFDC00) => text #0033D9
// ✅ All others => white
$invoice_title_bg = strtoupper(trim($_SESSION['invoice_data']['invoice_title_bg'] ?? '#FFDC00'));

// validate background
if (!in_array($invoice_title_bg, $allowedTitleBarColors, true)) {
    $invoice_title_bg = '#FFDC00';
}

// decide text color based on background
$invoice_title_text = ($invoice_title_bg === '#FFDC00') ? '#0033D9' : '#FFFFFF';

// persist
$_SESSION['invoice_data']['invoice_title_bg']   = $invoice_title_bg;
$_SESSION['invoice_data']['invoice_title_text'] = $invoice_title_text;

// If it's only digits, make it look like your INV format
if ($rawInvNo !== '' && preg_match('/^\d+$/', $rawInvNo)) {
    $rawInvNo = 'INV' . $rawInvNo;
}

// Keep invoice number for display under Invoice Date
$invoice_display_number = $rawInvNo;

// Title bar should NOT include invoice number anymore
$invoiceTitleText = 'INVOICE';

// If user selected a currency on this preview, use it; otherwise default from settings
$currency_code = strtoupper(trim($_POST['currency_code'] ?? ($_SESSION['invoice_data']['currency_code'] ?? $default_currency_code)));
if (!isset($allowedCurrencies[$currency_code])) {
  $currency_code = $default_currency_code;
}

// Display prefix like CA$, US$, etc.
$currency_display = $allowedCurrencies[$currency_code]['display'] ?? $default_currency_symbol;

// Persist on session so page reloads keep selection
$_SESSION['invoice_data']['currency_code']    = $currency_code;
$_SESSION['invoice_data']['currency_display'] = $currency_display;

// Load default banking details (from Settings → Payment Methods)
$bank_account_name    = get_setting('bank_account_name');
$bank_name            = get_setting('bank_name');
$bank_account_number  = get_setting('bank_account_number');
$bank_iban            = get_setting('bank_iban');
$bank_swift           = get_setting('bank_swift');
$bank_routing         = get_setting('bank_routing');
$bank_additional_info = get_setting('bank_additional_info');

// Prefer invoice-specific overrides if present in session data
$invoice_bank_account_name    = $data['bank_account_name']    ?? $bank_account_name;
$invoice_bank_name            = $data['bank_name']            ?? $bank_name;
$invoice_bank_account_number  = $data['bank_account_number']  ?? $bank_account_number;
$invoice_bank_iban            = $data['bank_iban']            ?? $bank_iban;
$invoice_bank_swift           = $data['bank_swift']           ?? $bank_swift;
$invoice_bank_routing         = $data['bank_routing']         ?? $bank_routing;
$invoice_bank_additional_info = $data['bank_additional_info'] ?? $bank_additional_info;

// Should banking details be shown on this invoice preview?
// (Will also be sent to save_invoice.php as 0/1)
$show_bank_details = isset($data['show_bank_details'])
    ? (int)$data['show_bank_details']
    : 0; // default hidden

// If user has no permission, force it OFF (cannot show on invoice)
if (!$can_toggle_bank_details) {
    $show_bank_details = 0;
}

// Recurring invoice flag (1 = recurring monthly, 0 = one-time)
$is_recurring = isset($data['is_recurring'])
    ? (int)$data['is_recurring']
    : 0; // default: not recurring
?>

<script>
  const PRICE_COL_IDX = <?= $priceColIndex !== null ? $priceColIndex : 'null' ?>;

  // ✅ Currency display for totals (from settings)
  let CURRENCY_DISPLAY = <?= json_encode($currency_display) ?>;
</script>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice Preview</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
      // Apply dark or light mode cleanly
      const root = document.documentElement;
      const isDark = localStorage.getItem('darkMode') === '1';
    
      root.classList.remove('dark-mode', 'light-mode');
      root.classList.add(isDark ? 'dark-mode' : 'light-mode');
  </script>
    
  <script>
      const STRIPE_MAX_TOTAL = 999999.99; // Stripe single payment hard limit
      const MANUAL_MODE = <?= $manual_mode ? 'true' : 'false' ?>;
  </script>

  <style>
      :root {
      --primary: #0033D9;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
    }
    
    .light-mode {
      --primary: #0033D9;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
    }
    
    .light-mode body {
      background-color: var(--body-bg);
      color: var(--dark);
    }
    
    .dark-mode body {
      background-color: var(--body-bg);
      color: var(--dark);
    }
    
    .dark-mode {
      --primary: #5a7dff;
      --primary-light: #6e8fff;
      --secondary: #4d45d1;
      --success: #5ed5f9;
      --danger: #ff3d96;
      --warning: #ffaa45;
      --dark: #e9ecef;
      --light: #212529;
      --gray: #adb5bd;
      --border: #495057;
      --card-bg: #2d3748;
      --body-bg: #1a202c;
    }

    .light-mode .invoice-box {
      background-color: var(--card-bg);
      color: var(--dark);
    }
    
    .light-mode .invoice-box td {
      color: var(--dark);
    }
    
    .light-mode .editable-cell {
      background-color: #fff9db !important;
      color: var(--dark);
    }
    
    .light-mode .readonly-cell {
      background-color: #f5f5f5 !important;
      color: #777;
    }
    
    .light-mode .main-content {
      background-color: var(--body-bg);
      color: var(--dark);
    }
    
    .dark-mode .main-content {
      background-color: var(--body-bg);
      color: var(--dark);
    }
    
    .light-mode input,
    .light-mode textarea,
    .light-mode select {
      background-color: #fff;
      color: var(--dark);
      border: 1px solid var(--border);
    }
    
    .light-mode .total-display,
    .light-mode .manual-total-container {
      background-color: var(--light);
      border: 1px solid var(--border);
      color: var(--dark);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--body-bg);
      color: var(--dark);
      margin: 0;
    }
    
    .invoice-box,
    .main-content,
    .card,
    .table,
    input,
    textarea,
    select {
      font-size: 14px;
    }
    
    .invoice-box {
      margin: auto;
      padding: 30px;
      border: 1px solid #eee;
      background: var(--card-bg);
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .company-info, .bill-to {
      font-size: 14px;
      line-height: 1.5;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 12px;
    }

    th, td {
      border: 1px solid var(--border);
      padding: 8px;
      vertical-align: top;
      text-align: left;
    }

    th {
      background-color: var(--light);
      font-weight: bold;
    }
    
    .btn {
      padding: 10px 20px;
      font-size: 16px;
      background-color: var(--primary);
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .btn:hover {
      background-color: #3f37c9;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    /* Recurring toggle button */
    .recurring-toggle {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 999px;
      border: none;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.25s ease, box-shadow 0.25s ease, transform 0.1s ease;
      color: #fff;
    }

    .recurring-toggle i {
      font-size: 14px;
    }

    .recurring-toggle.recurring-on {
      background-color: #16a34a; /* green */
      box-shadow: 0 4px 8px rgba(22, 163, 74, 0.35);
    }

    .recurring-toggle.recurring-off {
      background-color: #b91c1c; /* red */
      box-shadow: 0 4px 8px rgba(185, 28, 28, 0.35);
    }

    .recurring-toggle:active {
      transform: translateY(1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .recurring-row {
      margin-top: 16px;
      margin-bottom: 8px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    
    .recurring-row-label {
      font-size: 13px;
      color: var(--gray);
    }
    
    /* Strong, visible gap ONLY between Recurring row and the next section */
    .recurring-row {
      margin-bottom: 40px !important; /* bigger so you actually see it */
    }
    
    /* Extra safety: if Banking Details block comes right after, add top spacing too */
    .recurring-row + .form-group {
      margin-top: 20px !important;
    }

    .invoice-box table {
      table-layout: fixed;
      width: 100%;
      word-wrap: break-word;
    }
    
/* ===========================
   Invoice table: widths based on HEADER text
   (JS will set actual column widths)
   =========================== */
.invoice-table-scroll {
  width: 100%;
  overflow-x: hidden; /* ✅ default: no scrollbar */
  -webkit-overflow-scrolling: touch;
}

.invoice-table-scroll.has-x-scroll {
  overflow-x: auto; /* ✅ only when needed */
}


/* ✅ Step 5 FIX: if your layout uses flex anywhere, this prevents overflow push */
.main-content,
.card,
.invoice-box,
.invoice-table-scroll {
  min-width: 0;
  max-width: 100%;
}

/* Keep the table filling the container by default */
/* Allow JS to control min-width so page won't grow right */
#invoiceTable {
  width: 100%;
  min-width: 100%;              /* JS may increase this to px when needed */
  table-layout: fixed;
  border-collapse: collapse;
}

/* ✅ prevents 1–2px overflow (table border included in width:100%) */
#invoiceTable,
#invoiceTable th,
#invoiceTable td {
  box-sizing: border-box;
}

/* Headers should not wrap; their text defines the column width */
#invoiceTable thead th {
  white-space: nowrap !important;
}

/* Body can wrap */
#invoiceTable tbody td {
  white-space: normal !important;
}

    .invoice-box th, .invoice-box td {
      font-size: 11px;
      word-break: break-word;
      overflow: hidden;
      white-space: normal;
    }
    
    /* Hard enforce borders on the invoice preview table only */
    #invoiceTable,
    .invoice-table {
      border-collapse: collapse;
      border: 1px solid var(--border);
    }

    #invoiceTable th,
    #invoiceTable td,
    .invoice-table th,
    .invoice-table td {
      border: 1px solid var(--border) !important;
      padding: 8px;
      vertical-align: top;
      text-align: left;
    }

    
    /* === NEW === */
    .row-disabled {
      opacity: .45;
      background: var(--light) !important;
    }
    
    .row-disabled td:not(:first-child){
        pointer-events:none;
        background:#f5f5f5!important;
        color:#777;
    }

    .editable-cell {
      background-color: #fff9db !important;
    }
    
    /*  Place these two lines BELOW the .editable-cell rule  */
    .row-disabled .editable-cell,
    .row-disabled .readonly-cell{
        background: var(--light) !important;
        color:#777 !important;
        pointer-events:none !important;   /* <- kills editing */
    }
    
    /* Force blue background for invoice table headers */
    #invoiceTable thead th.header-cell,
    .invoice-table thead th.header-cell {
      background-color: var(--primary) !important;
      color: #fff !important;
      font-weight: 600;
    }

    .readonly-cell {
      background-color: #f5f5f5 !important;
      color: #777;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    .form-label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    .form-control {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid var(--border);
      border-radius: 5px;
      background: #fff;
      font-size: 14px;
    }
    
    /* Tight currency dropdown in Total Amount row (override .form-control width:100%) */
    .total-display #currency_code {
      width: auto !important;
      min-width: 56px !important;
      padding: 6px 6px !important;
    }

    .form-control:focus {
      border-color: #0033D9;
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    
    .flex-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
    }
    
    .date-section {
      display: flex;
      gap: 20px;
      margin: 20px 0;
    }
    .date-column {
      flex: 1;
    }
    
    .manual-total-container {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #eee;
      width: 250px;
    }
    
    .dark-mode .manual-total-container {
      background-color: #2d3748;
      border-color: var(--border);
    }
    
    .dark-mode .manual-total-container label {
      color: #e2e8f0 !important;
    }
    
    .total-display {
      font-size: 16px;
      font-weight: bold;
      text-align: right;
      padding: 10px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border: 1px solid #eee;
      min-width: 250px;
      width: fit-content;
      max-width: 420px;
      white-space: nowrap;
    }
    
    .dark-mode .total-display {
      background-color: #2d3748;
      color: #e2e8f0;
      border-color: var(--border);
    }
    
    .permission-note {
      color: var(--gray);
      font-size: 14px;
      margin-top: 10px;
    }
    
    .payment-method {
      margin-top: 10px;
      text-align: right;
      font-size: 14px;
      color: var(--gray);
    }
    
    .dark-mode .invoice-box td {
      color: var(--dark); /* Normally white text, but we're using your design tokens */
    }
    
    .dark-mode .invoice-box .editable-cell {
      background-color: #2d3748 !important;
      color: #e2e8f0 !important;
    }
    
    .dark-mode .invoice-box .readonly-cell {
      background-color: #1f2937 !important;
      color: #94a3b8 !important;
    }
    
    .dark-mode input,
    .dark-mode textarea,
    .dark-mode select {
      background-color: #2d3748;
      color: #e2e8f0;
      border-color: var(--border);
    }

    .dark-mode input::placeholder,
    .dark-mode textarea::placeholder {
      color: #94a3b8;
    }
    
    .dark-mode ::placeholder {
      color: #a0aec0;
    }
    
    .dark-mode input::placeholder,
    .dark-mode input::-webkit-input-placeholder,
    .dark-mode input:-ms-input-placeholder {
      color: #a0aec0 !important;
    }
    
    .dark-mode .form-label {
      font-weight: 600 !important;
    }
    
    input,
    textarea,
    select,
    button,
    label {
      font-family: inherit;
      font-size: 14px;
    }

    .invoice-header-section {
      display: flex;
      justify-content: space-between;
      margin-top: 6px;
      margin-bottom: 30px;
    }
    
        .column-toggle-wrapper {
      margin-top: 10px;
      margin-bottom: 8px;
      padding: 8px 10px;
      background: #f8f9fa;
      border: 1px solid var(--border);
      border-radius: 6px;
    }

    .column-toggle-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px 16px;
    }

    .column-toggle-item {
      font-size: 12px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 6px;
      border-radius: 4px;
      background: #ffffff;
      border: 1px solid #e0e0e0;
    }

    .column-toggle-item input[type="checkbox"] {
      margin: 0;
    }

    .column-toggle-item.price-column-label {
      border-color: var(--primary);
      background: #e9f0ff;
    }

    .required-pill {
      font-size: 10px;
      padding: 1px 6px;
      border-radius: 999px;
      background: var(--primary);
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    
    .company-info,
    .bill-to {
      font-family: inherit;
      font-size: 14px;
      line-height: 1.5;
    }
    
    .company-name {
      font-weight: 700;
      font-size: 16px;
      margin-bottom: 4px;
    }
    
    .bill-to {
      text-align: right;
    }
        .column-toggle-bar {
      margin-top: 10px;
      margin-bottom: 6px;
      font-size: 12px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
    }

    .column-toggle-bar strong {
      margin-right: 4px;
    }

    .column-toggle-item {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 6px;
      border-radius: 4px;
      background: rgba(67, 97, 238, 0.06);
    }

    .stripe-warning {
      margin-top: 10px;
      padding: 10px 12px;
      border-radius: 6px;
      border: 1px solid var(--warning);
      background: #fff8e6;
      color: #7c3a00;
      font-size: 12px;
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }

    .stripe-warning.hidden {
      display: none;
    }

    .dark-mode .stripe-warning {
      background: #45260a;
      border-color: #f6ad55;
      color: #fefcbf;
    }

    .btn-disabled-stripe {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
        .bank-drawer {
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transform: translateY(-4px);
      transition: max-height 0.3s ease, opacity 0.25s ease, transform 0.25s ease;
    }

    .bank-drawer.open {
      max-height: 600px; /* plenty of space for all fields */
      opacity: 1;
      transform: translateY(0);
      margin-top: 8px;
    }

    /* ─────────────────────────────────────────────
     Invoice Title Bar Color Picker (Preview Page)
    ───────────────────────────────────────────── */
    .titlebar-picker {
      margin-top: 12px;
      padding: 12px;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--light);
    }
    
    .dark-mode .titlebar-picker {
      background: #1f2937;
      border-color: var(--border);
    }
    
    .color-swatch-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
      align-items: center;
    }
    
    .color-swatch {
      border: 2px solid transparent;
      background: transparent;
      padding: 2px;            /* small click target padding */
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.12s ease, border-color 0.2s ease, box-shadow 0.2s ease;
      line-height: 0;
    }
    
    .color-swatch:active { transform: translateY(1px); }
    
    .color-swatch .swatch-box {
      width: 26px;             /* ✅ smaller */
      height: 26px;            /* ✅ smaller */
      border-radius: 8px;
      box-shadow: none;        /* ✅ clean, no heavy shadow */
    }
    
    .color-swatch.is-selected {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.18);
    }
    
    .invoice-title-preview {
      margin-top: 12px;
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 16px;
      font-weight: 800;
      text-align: center;
      color: inherit; /* allow JS/PHP to set color */
      letter-spacing: .03em;
    }

  </style>
  <?php if (!$can_edit_invoice): ?>
    <style>
      td[contenteditable="true"] {
        background-color: #f8f9fa !important;
        border: none !important;
        pointer-events: none !important;
      }
    </style>
  <?php endif; ?>

</head>
<body>
<?php require 'header.php'; ?>
<div class="app-container">
  <?php require 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <div class="page-title">Invoice Preview</div>
        <div class="page-actions">
        <?php if ($can_save_invoice): ?>
          <button type="submit" form="invoiceForm" class="btn" id="saveInvoiceBtn">Save Invoice</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="invoice-box">    
      <form method="post" action="save_invoice.php" id="invoiceForm">
        <input type="hidden" name="price_mode" value="<?= $manual_mode ? 'manual' : 'column' ?>">
        <input type="hidden" name="price_column" value="<?= htmlspecialchars($selected_col ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <?php
      // Adjust Bill-To vertical offset when a logo exists
      $bill_to_offset = !empty($company_logo) ? 'margin-top:74px;' : '';
    ?>
      <!-- Row 1 – logo + Bill-To heading -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div>
            <?php if(!empty($company_logo)): ?>
              <img src="<?= htmlspecialchars($company_logo) ?>" alt="Logo" style="max-height:70px;">
            <?php endif; ?>
          </div>
          <div style="font-size:16px;font-weight:700;text-align:right;">Bill&nbsp;To:</div>
        </div>
        
        <!-- Row 2 – company block vs. client block -->
        <div class="invoice-header-section">
          <div class="company-info">
            <div class="company-name"><?= htmlspecialchars($company_name) ?></div>
            <div><?= nl2br(htmlspecialchars($company_address)) ?></div>
            <div><?= htmlspecialchars($company_phone) ?></div>
            <div><?= htmlspecialchars($company_email) ?></div>
            <div>GST/HST: <?= htmlspecialchars($gst_number) ?></div>
          </div>
        
          <div class="bill-to">
            <?php foreach ($data['bill_to'] as $lbl=>$val): ?>
              <?php if(!empty($val)): ?><div><?= htmlspecialchars($val) ?></div><?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        
        <!-- Invoice Title Bar Color (controls the PDF heading section) -->
        <div class="titlebar-picker">
          <div class="form-label" style="margin:0;">
            <strong>Invoice Title Bar Color (PDF Heading)</strong>
            <div style="font-size:12px;color:var(--gray);margin-top:4px;">
            </div>
          </div>
        
          <div class="color-swatch-row" id="titleBarColorRow">
            <?php
              $titleColors = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];
              foreach ($titleColors as $c):
                $selected = (strtoupper($c) === strtoupper($invoice_title_bg));
            ?>
              <button
                type="button"
                class="color-swatch <?= $selected ? 'is-selected' : '' ?>"
                data-color="<?= htmlspecialchars($c) ?>"
                aria-label="Select <?= htmlspecialchars($c) ?>"
                title="<?= htmlspecialchars($c) ?>"
              >
                <div class="swatch-box" style="background: <?= htmlspecialchars($c) ?>;"></div>

              </button>
            <?php endforeach; ?>
          </div>
        
          <!-- Small preview bar (just to confirm the selected color) -->
          <div
              id="invoiceTitlePreview"
              class="invoice-title-preview"
              style="background: <?= htmlspecialchars($invoice_title_bg) ?>; color: <?= htmlspecialchars($invoice_title_text) ?>;"
            >
            <?= htmlspecialchars($invoiceTitleText) ?>
          </div>
        </div>
      
    <!-- Invoice Table -->
      <?php if (!empty($_POST['custom_table_html'])): ?>
        <?= $_POST['custom_table_html'] ?>
      <?php else: ?>

        <!-- Column selector (above table) -->
        <div class="column-toggle-wrapper">
          <div class="form-label" style="margin-bottom:6px;"><strong>Columns to include:</strong></div>
          <div class="column-toggle-list">
            <?php
              // if row-checkbox column is present, table data columns start from index 1
              $colOffset = $can_show_checkboxes ? 1 : 0;
              $idx = 0;
              foreach ($includeCols as $colLabel):
                  $domIndex   = $idx + $colOffset; // real <th>/<td> index in the table
                  // Treat as price column if label matches the selected price column in auto mode
                  $isPriceCol = (!$manual_mode && $selected_col && $colLabel === $selected_col);
            ?>
              <label class="column-toggle-item<?= $isPriceCol ? ' price-column-label' : '' ?>">
                <input
                  type="checkbox"
                  class="col-toggle"
                  data-col-idx="<?= $domIndex ?>"
                  data-col-name="<?= htmlspecialchars($colLabel, ENT_QUOTES) ?>"
                  <?= $isPriceCol ? 'data-price-col="1" checked disabled' : 'checked' ?>
                >
                <?= htmlspecialchars($colLabel) ?>
                <?php if ($isPriceCol): ?>
                  <span class="required-pill">Required for total</span>
                <?php endif; ?>
              </label>
            <?php
                $idx++;
              endforeach;
            ?>
          </div>
        </div>

       <div class="invoice-table-scroll">
        <table id="invoiceTable">
          <colgroup id="invoiceColgroup"></colgroup>
            <thead>
            <tr>
              <?php if ($can_show_checkboxes): ?>
                <th style="width: 30px;">
                  <input type="checkbox" id="selectAll" checked>
                </th>
              <?php endif; ?>

              <?php foreach ($includeCols as $col): ?>
                  <th
                    <?php if ($can_edit_invoice): ?>
                      contenteditable="true" class="header-cell"
                    <?php else: ?>
                      class="header-cell"
                    <?php endif; ?>
                  >
                    <?= htmlspecialchars($col) ?>
                  </th>
                <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data['items'] as $row): ?>
              <tr class="data-row">
                <?php if ($can_show_checkboxes): ?>
                  <td><input type="checkbox" class="rowCheckbox" checked></td>
                <?php endif; ?>
              <?php foreach ($includeCols as $col): ?>
                <?php
                  $rawValue = $row[$col] ?? '';
                    $isPrice  = (!$manual_mode && $col === $selected_col);
                    
                    // Normalize price column values using the same logic as the backend total
                    if ($isPrice) {
                        $amount = parseAmount($rawValue);
                        $rawValue = $amount !== 0.0 ? number_format($amount, 2) : '';
                    }
                    
                    $classes = [];
                    if ($isPrice)          $classes[] = 'amount';
                    if ($can_edit_invoice) $classes[] = 'editable-cell';
                    else                   $classes[] = 'readonly-cell';
                    
                    $classAttr = 'class="' . implode(' ', $classes) . '"';
                    $editAttr  = $can_edit_invoice ? 'contenteditable="true"' : '';
                    ?>
                    <td <?= $classAttr ?> <?= $editAttr ?>>
                      <?= htmlspecialchars($rawValue) ?>
                    </td>
              <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
       </div>
      <?php endif; ?>

      <!-- Total Amount Section -->
      <div class="flex-container">
        <?php if ($can_add_field): ?>
          <button type="button" id="addFieldBtn" class="btn">+ Add Field</button>
        <?php endif; ?>
        
        <?php if ($manual_mode): ?>
          <div class="manual-total-container">
              <label class="form-label">Invoice Amount:</label>
            
              <div style="display:flex; gap:10px; align-items:center;">
                <select name="currency_code"
                        id="currency_code"
                        class="form-control"
                        style="width:110px; padding:6px 10px; font-size:14px;">
                  <?php foreach ($allowedCurrencies as $code => $meta): ?>
                      <option value="<?= htmlspecialchars($code) ?>"
                              data-display="<?= htmlspecialchars($meta['display']) ?>"
                              <?= $currency_code === $code ? 'selected' : '' ?>>
                        <?= htmlspecialchars($meta['label']) ?>
                      </option>
                  <?php endforeach; ?>
                </select>
            
                <input
                  type="number" step="0.01" min="0"
                  name="manual_total" id="manualTotal"
                  class="form-control" required
                  placeholder="Enter invoice total"
                  style="flex:1;"
                >
              </div>
            </div>
        <?php else: ?>
          <div class="total-display" style="display:flex;justify-content:flex-end;align-items:center;gap:7px;">
              <div style="font-weight:700;">Total Amount:</div>
            
              <!-- Currency dropdown (replaces the old CA$ prefix) -->
              <select name="currency_code"
                      id="currency_code"
                      class="form-control"
                      style="width:auto; min-width:70px; padding:6px 8px; font-size:14px;">
                <?php foreach ($allowedCurrencies as $code => $meta): ?>
                  <option value="<?= htmlspecialchars($code) ?>"
                          data-display="<?= htmlspecialchars($meta['display']) ?>"
                          <?= $currency_code === $code ? 'selected' : '' ?>>
                    <?= htmlspecialchars($meta['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            
              <!-- Amount only (no prefix here anymore) -->
              <span id="totalAmount" style="text-align:right; display:inline-block;">
                <?= number_format($sum, 2) ?>
              </span>
            </div>
        <?php endif; ?>
      </div>

      <!-- Stripe limit warning (shown only if total > Stripe max) -->
      <div id="stripeLimitWarning" class="stripe-warning hidden">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
          <div><strong>Online payment limit reached</strong></div>
          <div style="margin-top:4px;">
            Stripe Checkout has a maximum single payment limit of
            <strong><span class="currencyPrefix"><?= htmlspecialchars($currency_display) ?></span><?= number_format(999999.99, 2) ?></strong>.<br>
            This invoice's total is currently
            <strong><span class="currencyPrefix"><?= htmlspecialchars($currency_display) ?></span><span id="stripeLimitDisplay"><?= number_format($sum, 2) ?></span></strong>,
            so your client will <u>not</u> be able to pay via the Pay&nbsp;Now button.
          </div>
          <div style="margin-top:6px; font-size:12px;">
            You can:
            <ul style="margin:4px 0 0 18px; padding:0;">
              <li>Split this invoice into multiple smaller invoices, <em>or</em></li>
              <li>Issue this invoice for manual payment (bank transfer, cheque, etc.)
                  and mark it as <strong>Paid</strong> later on the Invoice History page.</li>
            </ul>
          </div>
          <label style="display:block; margin-top:8px; font-size:12px;">
            <input type="checkbox" id="manualOnlyAck">
            I understand that Stripe will not be available for this invoice. Create it for manual payment only.
          </label>
        </div>
      </div>

      <!-- Date Pickers -->
      <div class="date-section">
        <div class="date-column">
          <?php if ($can_show_invoice_date): ?>
            <div class="form-group">
              <label for="invoice_date" class="form-label"><strong>Invoice Date:</strong></label>
              <input type="date" id="invoice_date" name="invoice_date" class="form-control" required>
              
              <?php if (!empty($invoice_display_number)): ?>
                  <div style="margin-top:6px;">
                    <strong>Invoice #:</strong> <?= htmlspecialchars($invoice_display_number, ENT_QUOTES, 'UTF-8') ?>
                  </div>
              <?php endif; ?>
              
            </div>
          <?php endif; ?>
      
          <?php if ($can_show_invoice_time): ?>
            <div class="form-group">
              <label for="invoice_time" class="form-label"><strong>Invoice Time:</strong></label>
              <input type="time" id="invoice_time" name="invoice_time" step="60" class="form-control" required>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="date-column">
          <?php if ($can_show_due_date): ?>
            <div class="form-group">
              <label for="due_date" class="form-label"><strong>Due Date:</strong></label>
              <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>
          <?php endif; ?>
      
          <?php if ($can_show_due_time): ?>
            <div class="form-group">
              <label><input type="checkbox" id="toggle_due_time" name="include_due_time" onchange="toggleDueTime()"> Include Due Time</label>
              <div id="due_time_container" style="display: none; margin-top: 8px;">
                <label for="due_time" class="form-label"><strong>Due Time:</strong></label>
                <input type="time" id="due_time" name="due_time" step="60" class="form-control">
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <!-- Recurring Invoice Toggle -->
      <?php if ($can_manage_recurring): ?>
        <div class="recurring-row">
          <div class="recurring-row-label">
            <strong>Recurring Invoice:</strong>
            <span>Send this same amount to the same client every month on this invoice date.</span>
          </div>
          <button type="button"
                  id="recurringToggle"
                  class="recurring-toggle <?= $is_recurring ? 'recurring-on' : 'recurring-off' ?>">
            <i class="fas fa-sync-alt"></i>
            <span id="recurringToggleText">
              <?= $is_recurring ? 'Enabled (Monthly)' : 'Disabled (One-time)' ?>
            </span>
          </button>
        </div>
      <?php endif; ?>

      <?php if ($can_toggle_bank_details): ?>
      <div class="form-group"
           style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
          <label class="form-label"><strong>Banking Details (for this invoice)</strong></label>
          <p style="font-size: 12px; color: var(--gray); margin: 4px 0 10px;">
            These fields are pre-filled from Settings → Payment Methods.
            You can adjust them for this invoice only.
          </p>
        </div>

        <label style="font-size:13px; white-space:nowrap; cursor:pointer;">
          <input type="checkbox"
                 id="toggleBankDetails"
                 <?= $show_bank_details ? 'checked' : '' ?>>
          Show on this invoice
        </label>
      </div>
      <?php endif; ?>

        <!-- Drawer that opens/closes -->
        <div id="bankingDrawer" class="bank-drawer<?= $show_bank_details ? ' open' : '' ?>">

          <div class="date-section">
            <div class="date-column">
              <div class="form-group">
                <label class="form-label">Account Holder Name</label>
                <input type="text"
                       name="bank_account_name"
                       id="bank_account_name"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_account_name) ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Bank Name</label>
                <input type="text"
                       name="bank_name"
                       id="bank_name"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_name) ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Account Number</label>
                <input type="text"
                       name="bank_account_number"
                       id="bank_account_number"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_account_number) ?>">
              </div>
            </div>

            <div class="date-column">
              <div class="form-group">
                <label class="form-label">IBAN</label>
                <input type="text"
                       name="bank_iban"
                       id="bank_iban"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_iban) ?>">
              </div>

              <div class="form-group">
                <label class="form-label">SWIFT / BIC</label>
                <input type="text"
                       name="bank_swift"
                       id="bank_swift"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_swift) ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Routing / Sort Code</label>
                <input type="text"
                       name="bank_routing"
                       id="bank_routing"
                       class="form-control"
                       value="<?= htmlspecialchars($invoice_bank_routing) ?>">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Additional Payment Instructions</label>
            <textarea name="bank_additional_info"
                      id="bank_additional_info"
                      class="form-control"
                      rows="3"><?= htmlspecialchars($invoice_bank_additional_info) ?></textarea>
          </div>

        </div> <!-- /bankingDrawer -->
      </div>

    <!-- Hidden Fields -->
    <input type="hidden" name="invoice_title_bg" id="invoice_title_bg" value="<?= htmlspecialchars($invoice_title_bg) ?>">
      <input type="hidden" name="invoice_title_text" id="invoice_title_text" value="<?= htmlspecialchars($invoice_title_text) ?>">
      <input type="hidden"
             name="show_bank_details"
             id="showBankDetailsFlag"
             value="<?= $show_bank_details ? '1' : '0' ?>">

      <?php if ($can_manage_recurring): ?>
        <input type="hidden"
               name="is_recurring"
               id="isRecurringField"
               value="<?= $is_recurring ? '1' : '0' ?>">
      <?php endif; ?>

      <textarea name="invoice_html" hidden id="invoiceHTML"></textarea>
      <input type="hidden" name="invoice_total" id="invoiceTotal" value="<?= $sum ?>">
      <input type="hidden" name="skip_stripe" id="skipStripe" value="0">
      <input type="hidden" name="currency_display" id="currency_display" value="<?= htmlspecialchars($currency_display) ?>">

      <?php if (!empty($data['bill_to'])): ?>
        <?php foreach ($data['bill_to'] as $key => $value): ?>
          <input type="hidden" name="bill_to[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($value) ?>">
        <?php endforeach; ?>
      <?php endif; ?>
      
      <!-- Submit Button -->
          <?php if ($can_save_invoice): ?>
              <?php if (!$can_edit_invoice): ?>
                <div class="permission-note">You do not have permission to edit this invoice, but you can still proceed to save the current version.</div>
              <?php endif; ?>
            <?php else: ?>
              <button type="submit" disabled class="btn">Save Invoice</button>
              <div class="permission-note">You do not have permission to save this invoice.</div>
            <?php endif; ?>
          </div>
        </form>
    <div style="height: 30px;"></div>

    <?php if ($final_payment_method): ?>
      <div class="payment-method">
        <strong>Payment Method:</strong> <?= htmlspecialchars($final_payment_method) ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function toggleDueTime() {
    const cb = document.getElementById('toggle_due_time');
    const container = document.getElementById('due_time_container');
    if (!cb) return;
    
    if (cb.checked) {
      container.style.display = 'block';
    } else {
      container.style.display = 'none';
      document.getElementById('due_time').value = '';
    }
  }

   function updateTotal() {
    let total = 0;
    document.querySelectorAll('tr.data-row').forEach(row => {
      const cb = row.querySelector('.rowCheckbox');
      const includeRow = (!cb) || cb.checked;
      if (!includeRow || row.style.display === 'none') return;

      const amtCell = row.querySelector('.amount');
      if (!amtCell) return;

      const num = parseFloat(amtCell.textContent.replace(/[^0-9.]/g, ''));
      if (!isNaN(num)) total += num;
    });

    const totalSpan  = document.getElementById('totalAmount');
    const totalInput = document.getElementById('invoiceTotal');

    if (totalSpan) {
      totalSpan.textContent = total.toFixed(2);
    }

    if (totalInput) {
      totalInput.value = total.toFixed(2);
    }

    checkStripeLimit();
  }
  
    function checkStripeLimit() {
    const totalInput = document.getElementById('invoiceTotal');
    const saveBtn    = document.getElementById('saveInvoiceBtn');
    const warning    = document.getElementById('stripeLimitWarning');
    const ack        = document.getElementById('manualOnlyAck');
    const display    = document.getElementById('stripeLimitDisplay');
    const skipStripe = document.getElementById('skipStripe');

    if (!totalInput || !saveBtn || !warning || typeof STRIPE_MAX_TOTAL === 'undefined') {
      return;
    }

    const total = parseFloat(totalInput.value || '0');

    if (!isNaN(total) && total > STRIPE_MAX_TOTAL) {
      warning.classList.remove('hidden');
      if (display) {
        display.textContent = total.toFixed(2);
      }

      if (ack) {
        if (!ack.checked) {
          // User has NOT acknowledged → block Save
          saveBtn.disabled = true;
          saveBtn.classList.add('btn-disabled-stripe');
          if (skipStripe) skipStripe.value = '0';
        } else {
          // User accepted manual-only mode
          saveBtn.disabled = false;
          saveBtn.classList.remove('btn-disabled-stripe');
          if (skipStripe) skipStripe.value = '1'; // tell save_invoice.php to skip Stripe
        }
      }
    } else {
      // Under or equal to the Stripe limit → hide warning and reset
      warning.classList.add('hidden');
      if (ack) {
        ack.checked = false;
      }
      if (skipStripe) {
        skipStripe.value = '0';
      }
      saveBtn.disabled = false;
      saveBtn.classList.remove('btn-disabled-stripe');
    }
  }
  
  function measureTextPx(text, font) {
  const canvas = measureTextPx._c || (measureTextPx._c = document.createElement('canvas'));
  const ctx = canvas.getContext('2d');
  ctx.font = font || '12px Arial';
  return Math.ceil(ctx.measureText(String(text || '')).width);
}

function syncInvoiceScrollbar() {
  const table = document.getElementById('invoiceTable');
  if (!table) return;

  const wrap = table.closest('.invoice-table-scroll');
  if (!wrap) return;

  // give layout a tick to update
  requestAnimationFrame(() => {
    const EPS = 2; // tolerance for rounding
    const hasOverflow = table.scrollWidth > wrap.clientWidth + EPS;
    wrap.classList.toggle('has-x-scroll', hasOverflow);
  });
}

function applyHeaderBasedWidths() {
  const table = document.getElementById('invoiceTable');
  if (!table || !table.tHead || !table.tHead.rows.length) return;

  const scrollWrap = table.closest('.invoice-table-scroll');
  const containerW = scrollWrap ? scrollWrap.clientWidth : (table.parentElement ? table.parentElement.clientWidth : 0);

  const headerRow = table.tHead.rows[0];
  const colgroup  = document.getElementById('invoiceColgroup');
  if (!colgroup) return;

  const colCount = headerRow.cells.length;

  // Ensure colgroup has correct number of <col>
  while (colgroup.children.length < colCount) colgroup.appendChild(document.createElement('col'));
  while (colgroup.children.length > colCount) colgroup.removeChild(colgroup.lastChild);

  const hasRowCheckbox = !!document.getElementById('selectAll');
  const fixedIdx = hasRowCheckbox ? 0 : -1;
  const fixedW   = hasRowCheckbox ? 34 : 0;

  const base = new Array(colCount).fill(0);
  let sumNonFixed = 0;
  let totalPx = 0;

  const visible = new Array(colCount).fill(true);

  // Build widths from HEADER text only
  for (let i = 0; i < colCount; i++) {
    const th  = headerRow.cells[i];
    const col = colgroup.children[i];

    const hidden = th && th.style && th.style.display === 'none';
    if (hidden) {
      visible[i] = false;
      col.style.display = 'none';
      col.style.width = '0px';
      continue;
    }

    col.style.display = '';
    visible[i] = true;

    if (i === fixedIdx) {
      base[i] = fixedW;
    } else {
      const cs = window.getComputedStyle(th);
      const font = (cs.font && cs.font !== '') ? cs.font : `${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
      const textW = measureTextPx(th.textContent.trim(), font);

      const padding = 26;
      const minW = 70;
      base[i] = Math.max(minW, textW + padding);
      sumNonFixed += base[i];
    }

    totalPx += base[i];
  }

  // Always keep table width 100% so page never stretches
  table.style.width = '100%';

  // If we know container width, scale columns to fit (shrink OR grow)
  if (containerW > 0 && sumNonFixed > 0) {
    const targetNonFixed = Math.max(0, containerW - fixedW);
    const scale = targetNonFixed / sumNonFixed;

    totalPx = fixedW;

    for (let i = 0; i < colCount; i++) {
      if (!visible[i]) continue;

      const col = colgroup.children[i];

      if (i === fixedIdx) {
        col.style.width = fixedW + 'px';
        continue;
      }

      const minW = 70;
      const w = Math.max(minW, Math.round(base[i] * scale));
      col.style.width = w + 'px';
      totalPx += w;
    }
  } else {
    // fallback: apply base widths
    for (let i = 0; i < colCount; i++) {
      if (!visible[i]) continue;
      const col = colgroup.children[i];
      const w = base[i] || (i === fixedIdx ? fixedW : 70);
      col.style.width = w + 'px';
    }
  }

  // Decide if wrapper needs scrollbar
  const EPS = 3;
  table.style.minWidth = (containerW > 0 && totalPx > containerW + EPS) ? (totalPx + 'px') : '100%';

  // ✅ this is what makes the scrollbar disappear instantly when it’s not needed
  syncInvoiceScrollbar();
}

  function toggleColumn(colIdx, visible) {
  const table = document.getElementById('invoiceTable');
  if (!table) return;

  Array.from(table.rows).forEach(row => {
    const cell = row.cells[colIdx];
    if (cell) cell.style.display = visible ? '' : 'none';
  });

  // Also hide/show the <col> so widths recalc correctly
  const cg = document.getElementById('invoiceColgroup');
  if (cg && cg.children[colIdx]) {
    cg.children[colIdx].style.display = visible ? '' : 'none';
  }

  // Re-apply header-based sizing to remove right-side gaps
  requestAnimationFrame(applyHeaderBasedWidths);
}
  
  // === REVISED toggleRow ===
function toggleRow(rowCb) {
  const row     = rowCb.closest('tr');
  const disable = !rowCb.checked;

  row.classList.toggle('row-disabled', disable);

  row.querySelectorAll('td').forEach((td, idx) => {
    if (idx === 0) return;                       // skip the checkbox cell

    if (disable) {
      /* ----- HARD LOCK ----- */
      td.setAttribute('contenteditable', 'false');  // <- immediately ends editing
      td.classList.add('readonly-cell');
      td.classList.remove('editable-cell');
      if (document.activeElement === td) td.blur(); // kick out active caret
    } else if (<?= $can_edit_invoice ? 'true' : 'false' ?>) {
      /* ----- ENABLE AGAIN (only if user has permission) ----- */
      td.setAttribute('contenteditable', 'true');
      td.classList.add('editable-cell');
      td.classList.remove('readonly-cell');
    }
  });

  updateTotal();
}

    document.addEventListener('DOMContentLoaded', () => {
    // Set current date/time
    const now = new Date();
    const d = document.getElementById('invoice_date');
    const t = document.getElementById('invoice_time');
    if (d) d.value = now.toISOString().slice(0, 10);
    if (t) t.value = now.toTimeString().slice(0, 5);
    
    // Set due date to 14 days from now
    const dueDate = document.getElementById('due_date');
    if (dueDate) {
      const due = new Date();
      due.setDate(due.getDate() + 14);
      dueDate.value = due.toISOString().slice(0, 10);
    }

    // Initialize editable cells
    document.querySelectorAll('.amount').forEach(cell => {
      if (cell.classList.contains('editable-cell')) {
        cell.addEventListener('input', updateTotal);
      }
    });
    
    // Initialize total for automatic mode
    <?php if (!$manual_mode): ?>
      updateTotal();
    <?php endif; ?>
    
    // Handle manual total input (manual mode)
    const manualInput = document.getElementById('manualTotal');
    if (manualInput) {
      manualInput.addEventListener('input', function() {
        const value = parseFloat(this.value) || 0;
        document.getElementById('invoiceTotal').value = value.toFixed(2);
        checkStripeLimit();
      });
    }

    // Hook Stripe warning acknowledgment
    const ack = document.getElementById('manualOnlyAck');
    if (ack) {
      ack.addEventListener('change', checkStripeLimit);
    }

    // Banking details drawer toggle
    const bankToggle = document.getElementById('toggleBankDetails');
    const bankDrawer = document.getElementById('bankingDrawer');
    const bankFlag   = document.getElementById('showBankDetailsFlag');

    if (bankToggle && bankDrawer && bankFlag) {
      const applyBankState = () => {
        if (bankToggle.checked) {
          bankDrawer.classList.add('open');
          bankFlag.value = '1';
        } else {
          bankDrawer.classList.remove('open');
          bankFlag.value = '0';
        }
      };

      bankToggle.addEventListener('change', applyBankState);
      applyBankState(); // ensure correct initial state
    }

    // Recurring invoice toggle
    const recurringToggle = document.getElementById('recurringToggle');
    const recurringField  = document.getElementById('isRecurringField');
    const recurringText   = document.getElementById('recurringToggleText');

    if (recurringToggle && recurringField && recurringText) {
      const applyRecurringState = () => {
        const isOn = recurringField.value === '1';
        recurringToggle.classList.toggle('recurring-on', isOn);
        recurringToggle.classList.toggle('recurring-off', !isOn);
        recurringText.textContent = isOn
          ? 'Enabled (Monthly)'
          : 'Disabled (One-time)';
      };

      recurringToggle.addEventListener('click', () => {
        // Flip between 0 and 1
        recurringField.value = (recurringField.value === '1') ? '0' : '1';
        applyRecurringState();
      });

      // Initial state from hidden field (PHP default)
      applyRecurringState();
    }

    // Run once on load (in case total is already large)
      checkStripeLimit();

    /* =========================
       Currency dropdown live update
       ✅ Use the selected option text (comes from PHP $allowedCurrencies)
       ========================= */
    const ccySelect = document.getElementById('currency_code');
    const ccyHidden = document.getElementById('currency_display');
    
    const applyCurrency = () => {
      if (!ccySelect) return;
    
      const opt = ccySelect.options[ccySelect.selectedIndex];
      const display = (opt && opt.dataset && opt.dataset.display) ? opt.dataset.display.trim() : '';
      if (display) CURRENCY_DISPLAY = display;
    
      // Update Stripe warning prefix text everywhere
      document.querySelectorAll('.currencyPrefix').forEach(el => {
        el.textContent = CURRENCY_DISPLAY;
      });
    
      // Keep hidden field aligned for save_invoice.php
      if (ccyHidden) ccyHidden.value = CURRENCY_DISPLAY;
    
      // Refresh totals + Stripe checks (numbers stay numeric)
      if (!MANUAL_MODE) updateTotal();
      else checkStripeLimit();
    };
    
    if (ccySelect) {
      ccySelect.addEventListener('change', applyCurrency);
      applyCurrency(); // run once on load
    }

    /* ✅ Step 5: header-based column widths (prevents right-side whitespace) */
    applyHeaderBasedWidths();

    // Recompute on window resize (debounced)
    window.addEventListener('resize', () => {
      clearTimeout(window.__invResizeT);
      window.__invResizeT = setTimeout(applyHeaderBasedWidths, 120);
    });

    // Recompute when header text is edited (contenteditable <th>)
    const invTable = document.getElementById('invoiceTable');
    if (invTable && invTable.tHead) {
      invTable.tHead.addEventListener('input', (e) => {
        if (e.target && e.target.tagName === 'TH') {
          requestAnimationFrame(applyHeaderBasedWidths);
        }
      });
    }
  });

// Build clean invoice_html (only visible columns + rows) on submit
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('invoiceForm');
  if (!form) return;

  form.addEventListener('submit', function () {
    let html = '';
    const table = document.getElementById('invoiceTable');

    if (table) {
      const hasRowCheckbox = !!document.getElementById('selectAll');
      const headerRow = table.tHead ? table.tHead.rows[0] : table.rows[0];

      const visibleCols = [];

      if (headerRow) {
        for (let c = 0; c < headerRow.cells.length; c++) {
          // skip the row-select checkbox column
          if (hasRowCheckbox && c === 0) continue;

          const cell = headerRow.cells[c];
          const style = window.getComputedStyle(cell);
          if (style.display === 'none') continue; // hidden via column toggles

          visibleCols.push({
            index: c,
            label: cell.textContent.trim()
          });
        }
      }

      html += '<table class="invoice-table"><thead><tr>';
      visibleCols.forEach(col => {
        html += '<th>' + escapeHtml(col.label) + '</th>';
      });
      html += '</tr></thead><tbody>';

      const body = table.tBodies[0];
      const bodyRows = body ? Array.from(body.rows) : Array.from(table.rows).slice(1);

      bodyRows.forEach(row => {
        const cb = row.querySelector('.rowCheckbox');
        if (cb && !cb.checked) {
          // unchecked row = do not include in final invoice
          return;
        }

        html += '<tr>';
        visibleCols.forEach(col => {
          const cell = row.cells[col.index];
          let text = cell ? cell.textContent : '';
          text = text.replace(/\s+/g, ' ').trim();
          html += '<td>' + escapeHtml(text) + '</td>';
        });
        html += '</tr>';
      });

      html += '</tbody></table>';
    }

    // Make sure total is in sync with what's on screen
    if (MANUAL_MODE) {
      const manualInput = document.getElementById('manualTotal');
      const totalInput  = document.getElementById('invoiceTotal');

      if (manualInput && totalInput) {
        const v = parseFloat(manualInput.value || '0');
        totalInput.value = isNaN(v) ? '0.00' : v.toFixed(2);
      }
    } else {
      // uses the same logic you see on screen for auto totals
      updateTotal();
    }

    const totalInput = document.getElementById('invoiceTotal');
    const totalVal   = totalInput ? (totalInput.value || '0') : '0';

    const ccy = (typeof CURRENCY_DISPLAY !== 'undefined' ? CURRENCY_DISPLAY : '$');

    html += `<div style="margin-top:20px;text-align:right;font-size:16px;font-weight:bold;">
               Total Amount: ${escapeHtml(ccy)}${parseFloat(totalVal || '0').toFixed(2)}
             </div>`;

    const dbMethod = <?= json_encode($final_payment_method) ?>;
    if (dbMethod) {
      html += `<div style="margin-top:8px;text-align:right;font-size:14px;">
                 <strong>Payment Method:</strong> ${escapeHtml(dbMethod)}
               </div>`;
    }

    const hidden = document.getElementById('invoiceHTML');
    if (hidden) {
      hidden.value = html;
    }
  });
});

// Row + column checkbox handling
/* ---------- Row-checkbox & Column-checkbox handling ---------- */
document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('invoiceTable');
  if (!table) return; // safety if no default table is rendered

  const selectAll   = document.getElementById('selectAll');
  const colToggles  = document.querySelectorAll('.col-toggle');
  const hasRowCheck = !!selectAll; // true if first column is the row checkbox column

  // Column visibility toggles (lock price column)
  colToggles.forEach((cb, i) => {
    const isPriceCol = cb.dataset.priceCol === '1';

    // lock the price column visually + logically
    if (isPriceCol) {
      cb.checked  = true;
      cb.disabled = true;
    }

    cb.addEventListener('change', function () {
      // extra safety: never allow unchecking price column
      if (this.dataset.priceCol === '1') {
        this.checked = true;
        return;
      }

      // calculate real <th>/<td> index:
      //   if there's a row-checkbox column, data columns start at index 1
      const colIndex = i + (hasRowCheck ? 1 : 0);
      const show     = this.checked;

      // hide/show the actual table column
      toggleColumn(colIndex, show);

      // 🎨 visually dim the chip when turned off
      const wrapper = this.closest('.column-toggle-item');
      if (wrapper) {
        wrapper.style.opacity         = show ? '1' : '0.45';
        wrapper.style.backgroundColor = show ? '' : '#f1f3f5';
      }
    });
  });

  // delegate to table – fires for existing and future rows
  table.addEventListener('change', (e) => {
    if (e.target.classList.contains('rowCheckbox')) {
      toggleRow(e.target);
    }
  });

  // initialise every row once at startup
  table.querySelectorAll('.rowCheckbox').forEach(cb => toggleRow(cb));

  /* “Select All” checkbox */
  if (selectAll) {
    selectAll.addEventListener('change', () => {
      const isChecked = selectAll.checked;
      table.querySelectorAll('.rowCheckbox').forEach(cb => {
        cb.checked = isChecked;
        toggleRow(cb);
      });
    });
  }
});

  // Add field button functionality
  document.getElementById("addFieldBtn")?.addEventListener("click", () => {
  const table = document.getElementById("invoiceTable");
  const tbody = table.querySelector("tbody");
  const tr    = document.createElement("tr");
  tr.classList.add("data-row");

  let html = '';
  if (document.getElementById('selectAll')) {
    html += '<td><input type="checkbox" class="rowCheckbox" checked></td>';
  }

  const colCount = table.rows[0].cells.length;
  for (let i = 0; i < colCount; i++) {
    if (i === 0 && document.getElementById('selectAll')) continue;
    if (PRICE_COL_IDX !== null && i === PRICE_COL_IDX) {
      html += '<td class="amount editable-cell" contenteditable="true"></td>';
    } else {
      html += '<td class="editable-cell" contenteditable="true"></td>';
    }
  }

  tr.innerHTML = html;
  tbody.appendChild(tr);

  // checkbox logic (delegated handler above will catch this too)
  tr.querySelector('.rowCheckbox')?.addEventListener('change', function(){
    toggleRow(this);
  });

  // recalc on input in the new price cell(s)
  tr.querySelectorAll('.amount').forEach(cell => {
    cell.addEventListener('input', updateTotal);
  });
});
  </script>
  
<?php require 'scripts.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;

    const savedTheme = localStorage.getItem('darkMode');
    const isDark = savedTheme === '1';

    root.classList.toggle('dark-mode', isDark);
    root.classList.toggle('light-mode', !isDark);

    if (themeToggle) {
      const icon = themeToggle.querySelector('i');
      icon.className = `fas ${isDark ? 'fa-sun' : 'fa-moon'}`;

      themeToggle.addEventListener('click', () => {
        const nowDark = root.classList.toggle('dark-mode');
        root.classList.toggle('light-mode', !nowDark);
        localStorage.setItem('darkMode', nowDark ? '1' : '0');
        icon.className = `fas ${nowDark ? 'fa-sun' : 'fa-moon'}`;
      });
    }
  });
  
    document.addEventListener('DOMContentLoaded', () => {
      const row      = document.getElementById('titleBarColorRow');
      const bgInput  = document.getElementById('invoice_title_bg');
      const textInput= document.getElementById('invoice_title_text');
      const preview  = document.getElementById('invoiceTitlePreview');
    
      if (!row || !bgInput || !preview) return;
    
      const getTextColor = (bg) => {
        const c = String(bg || '').trim().toUpperCase();
        return (c === '#FFDC00') ? '#0033D9' : '#FFFFFF';
      };
    
      const setSelected = (color) => {
        const c = String(color || '#FFDC00').trim().toUpperCase();
    
        // update hidden fields
        bgInput.value = c;
        const tc = getTextColor(c);
        if (textInput) textInput.value = tc;
    
        // update preview
        preview.style.background = c;
        preview.style.color = tc;
    
        // update selected UI
        row.querySelectorAll('.color-swatch').forEach(b => b.classList.remove('is-selected'));
        row.querySelectorAll('.color-swatch').forEach(b => {
          const bc = String(b.dataset.color || '').trim().toUpperCase();
          if (bc === c) b.classList.add('is-selected');
        });
      };
    
      // Apply initial state on load
      setSelected(bgInput.value);
    
      // Click handler (event delegation)
      row.addEventListener('click', (e) => {
        const btn = e.target.closest('.color-swatch');
        if (!btn) return;
        setSelected(btn.dataset.color);
      });
    });
</script>
</body>
</html>