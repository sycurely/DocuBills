<?php
// ✅ Ensure session is available for permission + ownership checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANT: update_status.php must return clean JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// log errors instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/update_status_error.log');

header('Content-Type: application/json; charset=utf-8');


require_once 'config.php';
require_once 'mailer.php'; // ✅ Include mailer
require_once 'middleware.php'; // ✅ Load has_permission() and user context
require_once 'libs/dompdf/autoload.inc.php';

// ✅ If invoices.html accidentally contains FULL page HTML, only keep the first table
function extract_first_table_html($html) {
    $html = (string)$html;
    if ($html === '') return '';

    if (preg_match('/<table\b[^>]*>.*?<\/table>/is', $html, $m)) {
        return $m[0];
    }
    return $html; // already table-only or custom fragment
}

// ✅ Strip any PAID watermark remnants from previously generated HTML
function strip_paid_watermark_blocks($html) {
    $html = (string)$html;
    if ($html === '') return '';

    // Remove our own inserted watermark block (we'll add it cleanly again if needed)
    $html = preg_replace('/<!--DB_PAID_WATERMARK-->.*?<!--\/DB_PAID_WATERMARK-->/is', '', $html);

    // Remove common CSS watermark (pseudo-element)
    $html = preg_replace('/\.invoice-container::before\s*\{[^}]*content\s*:\s*[\'"]PAID[\'"][^}]*\}\s*/is', '', $html);

    // Remove any fixed/absolute overlay div that contains ONLY "PAID"
    $html = preg_replace('/<div[^>]*style="[^"]*(position\s*:\s*(fixed|absolute))[^"]*"[^>]*>\s*PAID\s*<\/div>/is', '', $html);

    // Remove any simple PAID div watermark
    $html = preg_replace('/<div[^>]*>\s*PAID\s*<\/div>/is', '', $html);

    return $html;
}

/**
 * Parse CC/BCC stored in DB as "a@b.com, c@d.com" or new lines, etc.
 */
function parse_email_list_db($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') return [];

    $parts = preg_split('/[,\n;]+/', $raw);
    $out = [];

    foreach ($parts as $p) {
        $e = strtolower(trim($p));
        if ($e === '') continue;
        if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
            $out[] = $e;
        }
    }

    return array_values(array_unique($out));
}

function apply_template_replacements($html, array $replacements) {
    $html = (string)$html;
    if ($html === '') return '';
    // Make sure all replacements are strings
    foreach ($replacements as $k => $v) {
        $replacements[$k] = (string)$v;
    }
    return str_replace(array_keys($replacements), array_values($replacements), $html);
}

function resolve_invoice_status_notification_slug(PDO $pdo, string $status): string {
    $status = strtolower(trim($status));
    $wantPaid = ($status === 'paid');

    // We resolve by LABEL (meaning) so you can change slugs anytime.
    $label = $wantPaid ? 'Invoice Paid' : 'Invoice Unpaid';

    // deleted filter (NULL + empty + 0000)
    $sql = "
        SELECT slug
        FROM notification_types
        WHERE LOWER(TRIM(label)) = LOWER(TRIM(?))
          AND (deleted_at IS NULL OR deleted_at='' OR deleted_at='0000-00-00 00:00:00')
        LIMIT 1
    ";
    $st = $pdo->prepare($sql);
    $st->execute([$label]);

    $slug = trim((string)$st->fetchColumn());
    return $slug; // may be '' if label not found
}

/**
 * Load email template directly from DB using notification_types.slug.
 * This matches your manage-email-templates.php setup (no mailer.php changes).
 *
 * Returns: ['subject' => '...', 'body' => '...', 'cc' => '...', 'bcc' => '...'] OR null
 */
function fetch_email_template_by_slug(PDO $pdo, $slug) {
    $slug = trim((string)$slug);
    if ($slug === '') return null;

    // columns
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM email_templates")->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (Exception $e) {
        return null;
    }
    $has = function($c) use ($cols) { return in_array($c, $cols, true); };

    if (!$has('assigned_notification_type')) return null;

    // body column (your schema)
    if ($has('template_html') && $has('html_content')) {
        $bodyExpr = "COALESCE(NULLIF(template_html,''), NULLIF(html_content,''))";
    } elseif ($has('template_html')) {
        $bodyExpr = "NULLIF(template_html,'')";
    } elseif ($has('html_content')) {
        $bodyExpr = "NULLIF(html_content,'')";
    } else {
        return null;
    }

    // subject (optional – your schema likely doesn't have it)
    $subjectExpr = ($has('subject') ? "subject" : "''");
    $ccExpr  = ($has('cc_emails')  ? "cc_emails"  : "''");
    $bccExpr = ($has('bcc_emails') ? "bcc_emails" : "''");

    // deleted filter (handle NULL + empty + 0000)
    $whereDeleted = $has('deleted_at')
        ? " AND (deleted_at IS NULL OR deleted_at='' OR deleted_at='0000-00-00 00:00:00') "
        : "";

    $orderParts = [];
    if ($has('updated_at')) $orderParts[] = "updated_at DESC";
    $orderParts[] = "id DESC";
    $order = " ORDER BY " . implode(", ", $orderParts) . " LIMIT 1 ";

    // get notification type label + id (if exists)
    $ntId = 0; $ntLabel = '';
    try {
        $st = $pdo->prepare("SELECT id, label FROM notification_types WHERE LOWER(TRIM(slug))=LOWER(TRIM(?)) LIMIT 1");
        $st->execute([$slug]);
        $nt = $st->fetch(PDO::FETCH_ASSOC);
        if ($nt) {
            $ntId = (int)($nt['id'] ?? 0);
            $ntLabel = trim((string)($nt['label'] ?? ''));
        }
    } catch (Exception $e) { /* ignore */ }

    // 1) EXACT match attempts: slug, label, id-as-string
    $sql = "SELECT {$subjectExpr} AS subject, {$bodyExpr} AS body, {$ccExpr} AS cc, {$bccExpr} AS bcc, assigned_notification_type
            FROM email_templates
            WHERE 1=1 {$whereDeleted}
              AND (
                LOWER(TRIM(assigned_notification_type)) = LOWER(TRIM(?))
                OR LOWER(TRIM(assigned_notification_type)) = LOWER(TRIM(?))
                OR TRIM(assigned_notification_type) = ?
              )
            {$order}";
    $st = $pdo->prepare($sql);
    $st->execute([$slug, $ntLabel, (string)$ntId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row && trim((string)($row['body'] ?? '')) !== '') return $row;

    // 2) TOKEN match (super tolerant)
    // e.g. marked_unpaid -> must contain "marked" and "unpaid" anywhere in assigned_notification_type
    $tokens = preg_split('/[_\s\-]+/', $slug);
    $tokens = array_values(array_filter($tokens, function($t){
        $t = trim((string)$t);
        return $t !== '' && strlen($t) >= 3;
    }));

    if (!empty($tokens)) {
        $w = [];
        $p = [];
        foreach ($tokens as $t) {
            $w[] = "LOWER(TRIM(assigned_notification_type)) LIKE ?";
            $p[] = '%' . strtolower($t) . '%';
        }
        if ($ntLabel !== '') {
            // also allow tokens from label
            $labelTokens = preg_split('/[_\s\-]+/', $ntLabel);
            $labelTokens = array_values(array_filter($labelTokens, function($t){
                $t = trim((string)$t);
                return $t !== '' && strlen($t) >= 3;
            }));
            foreach ($labelTokens as $t) {
                $w[] = "LOWER(TRIM(assigned_notification_type)) LIKE ?";
                $p[] = '%' . strtolower($t) . '%';
            }
        }

        // require at least the slug tokens to match (AND)
        $sql2 = "SELECT {$subjectExpr} AS subject, {$bodyExpr} AS body, {$ccExpr} AS cc, {$bccExpr} AS bcc, assigned_notification_type
                 FROM email_templates
                 WHERE 1=1 {$whereDeleted}
                   AND (" . implode(" AND ", array_slice($w, 0, count($tokens))) . ")
                 {$order}";
        $st2 = $pdo->prepare($sql2);
        $st2->execute(array_slice($p, 0, count($tokens)));
        $row2 = $st2->fetch(PDO::FETCH_ASSOC);
        if ($row2 && trim((string)($row2['body'] ?? '')) !== '') return $row2;
    }

    return null;
}

try {
    if (!isset($_POST['invoice_id']) || !isset($_POST['status'])) {
        throw new Exception("Missing required fields.");
    }

    $invoice_id = intval($_POST['invoice_id']);
    $status = $_POST['status'];
    $payment_method = $_POST['payment_method'] ?? null;
    $payment_proof_path = null;

    // File upload (if provided)
    if ($status === 'Paid' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $fileType = mime_content_type($_FILES['payment_proof']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Unsupported file type. Allowed: PDF, JPG, PNG.");
        }

        $uploadDir = __DIR__ . '/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'proof_' . $invoice_id . '_' . time() . '_' . basename($_FILES['payment_proof']['name']);
        $targetFile = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetFile)) {
            throw new Exception("Failed to upload payment proof.");
        }

        $payment_proof_path = 'uploads/payment_proofs/' . $filename;
    }

    // ✅ Only allow these statuses
    $allowedStatuses = ['Paid', 'Unpaid'];
    if (!in_array($status, $allowedStatuses, true)) {
        throw new Exception("Invalid status value.");
    }
    
    // ✅ Fetch invoice FIRST (so we can do permission + preserve existing proof if needed)
    $invoice_stmt = $pdo->prepare("
        SELECT id, status, invoice_number, client_id, payment_link, due_date, total_amount, invoice_date,
               created_by, payment_proof, payment_method, currency_code, currency_display, html, bill_to_json, bill_to_name
        FROM invoices
        WHERE id = ?
    ");
    $invoice_stmt->execute([$invoice_id]);
    $invoice_row = $invoice_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice_row) {
        throw new Exception("Invoice not found in database.");
    }
    
    $invoice_original = $invoice_row;   // ✅ keep original state for rollback on any failure
    $didUpdateInvoice = false;          // ✅ flip true after DB update succeeds
    
    // 🔐 Ownership / invoice logs permission check (BEFORE update)
    $currentUserId      = $_SESSION['user_id'] ?? null;
    $canViewInvoiceLogs = has_permission('view_invoice_logs');
    
    if (!$canViewInvoiceLogs) {
        if ($currentUserId === null || (int)$invoice_row['created_by'] !== (int)$currentUserId) {
            throw new Exception("You are not allowed to modify this invoice.");
        }
    }
    
    // ✅ Preserve existing proof/method if Paid but user didn’t upload again
    $existingProof  = $invoice_row['payment_proof'] ?? null;
    $existingMethod = $invoice_row['payment_method'] ?? null;
    
    $payment_method_to_save = $payment_method;
    
    // If Unpaid → clear method + proof (clean)
    if ($status === 'Unpaid') {
        $payment_method_to_save = null;
        $payment_proof_path     = null;
    } else {
        // Paid → if no new file uploaded, keep existing proof
        if ($payment_proof_path === null) {
            $payment_proof_path = $existingProof;
        }
        // Paid → if no method provided, keep existing method
        if ($payment_method_to_save === null || $payment_method_to_save === '') {
            $payment_method_to_save = $existingMethod;
        }
    }
    
    // ✅ Use final resolved payment method going forward (for footer injection etc.)
    $payment_method = $payment_method_to_save;
    
    // ✅ Now update DB (after permission check)
    $stmt = $pdo->prepare("
      UPDATE invoices
         SET status = ?,
             payment_method = ?,
             payment_proof = ?
       WHERE id = ?
    ");
    $stmt->execute([$status, $payment_method_to_save, $payment_proof_path, $invoice_id]);
    // ❌ DO NOT throw on rowCount() === 0 (it can be 0 even when successful)
    
    $didUpdateInvoice = true;

    // Fetch invoice record (include created_by for ownership check)
    $invoice_stmt = $pdo->prepare("
        SELECT 
            id, 
            invoice_number, 
            client_id, 
            payment_link, 
            due_date, 
            total_amount, 
            invoice_date,
            created_by
        FROM invoices 
        WHERE id = ?
    ");
    $invoice_stmt->execute([$invoice_id]);
    $invoice_row = $invoice_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice_row) {
        throw new Exception("Invoice not found in database.");
    }

    // 🔐 Ownership / invoice logs permission check
    $currentUserId      = $_SESSION['user_id'] ?? null;
    $canViewInvoiceLogs = has_permission('view_invoice_logs'); // same permission as in history.php

    // If user does NOT have invoice logs permission, they may only modify their own invoices
    if (!$canViewInvoiceLogs) {
        if ($currentUserId === null || (int)$invoice_row['created_by'] !== (int)$currentUserId) {
            throw new Exception("You are not allowed to modify this invoice.");
        }
    }
    
    $invoice_number = $invoice_row['invoice_number'] ?? null;
    $html_path      = __DIR__ . "/invoices/{$invoice_number}.html";
    $client_id      = $invoice_row['client_id'] ?? null;
    $due_date       = $invoice_row['due_date'] ?? ''; // ✅ moved here AFTER fetch
    
    if (!$invoice_number) {
        throw new Exception("Invoice number missing.");
    }
    
    // If no client assigned, skip email logic but still update status and regenerate invoice
    if (!$client_id) {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated (no client assigned for email).'
        ]);
        return;
    }

    // Fetch client info
    $client_stmt = $pdo->prepare("SELECT company_name, email FROM clients WHERE id = ?");
    $client_stmt->execute([$client_id]);
    $client = $client_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) throw new Exception("Client not found.");

    $client_email = $client['email'];
    $client_name = $client['company_name'];
    
    // ✅ Resolve slug from DB (so you can change slugs anytime)
    $notification_type = resolve_invoice_status_notification_slug($pdo, $status);
    
    if ($notification_type === '') {
        throw new Exception("Notification type not found in DB for: " . ($status === 'Paid' ? 'Invoice Paid' : 'Invoice Unpaid') . ". Please check Settings → Notification Types.");
    }
    
    // ✅ pre-check template NOW (before heavy work)
    $templateRow = fetch_email_template_by_slug($pdo, $notification_type);
    if (!$templateRow || trim((string)($templateRow['body'] ?? '')) === '') {
        throw new Exception("Email template not found for notification type: {$notification_type}. Please set it in Settings → Email Templates.");
    }

    // Load and modify HTML
    // ✅ NEW: Pull full invoice details from DB
    $invoice_stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
    $invoice_stmt->execute([$invoice_id]);
    $invoice_data = $invoice_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice_data) throw new Exception("Invoice not found.");
    
    // ─────────────────────────────────────────────
    // ✅ Invoice Title Bar Color (load from DB)
    // ─────────────────────────────────────────────
    $allowedTitleBarColors = ['#0033D9', '#169E18', '#000000', '#FFDC00', '#5E17EB'];
    $allowedTitleTextColors = ['#0033D9', '#FFFFFF'];
    
    // Background
    $invoice_title_bg = strtoupper(trim((string)($invoice_data['invoice_title_bg'] ?? '#FFDC00')));
    if (!in_array($invoice_title_bg, $allowedTitleBarColors, true)) {
        $invoice_title_bg = '#FFDC00';
    }
    
    // Text (rule-based fallback)
    $invoice_title_text = strtoupper(trim((string)($invoice_data['invoice_title_text'] ?? '')));
    if (!in_array($invoice_title_text, $allowedTitleTextColors, true)) {
        $invoice_title_text = ($invoice_title_bg === '#FFDC00') ? '#0033D9' : '#FFFFFF';
    }

    // ✅ Pull client info again
    $client_stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $client_stmt->execute([$invoice_data['client_id']]);
    $client = $client_stmt->fetch(PDO::FETCH_ASSOC);
    
    // ✅ Construct data array like save_invoice.php
    $items = [];
    if (!empty($invoice_data['items_serialized'])) {
        $items = unserialize($invoice_data['items_serialized']);
    }
    file_put_contents('log_debug_status.txt', "✅ Loaded " . count($items) . " items for invoice {$invoice_number}\n", FILE_APPEND);


        // ✅ Currency display for regen + fallback table + email (multi-currency safe)
    $cur = trim((string)($invoice_data['currency_display'] ?? ''));
    if ($cur === '') {
        $cur = trim((string)($invoice_data['currency_code'] ?? ''));
    }
    if ($cur === '') {
        $cur = 'CAD';
    }

    // ✅ Use the exact bill_to structure saved at invoice creation
    $bill_to = [];
    if (!empty($invoice_data['bill_to_json'])) {
        $decoded = json_decode($invoice_data['bill_to_json'], true);
        if (is_array($decoded)) {
            $bill_to = $decoded;
        }
    }

    // Fallback if bill_to_json is empty/invalid
    if (empty($bill_to)) {
        $bill_to = [
            'Company Name' => (string)($invoice_data['bill_to_name'] ?? ($client['company_name'] ?? '')),
            'Contact Name' => (string)($client['representative'] ?? ''),
            'Address'      => (string)($client['address'] ?? ''),
            'Phone'        => (string)($client['phone'] ?? ''),
            'Email'        => (string)($client['email'] ?? ''),
            'gst_hst'      => (string)($client['gst_hst'] ?? ''),
            'notes'        => (string)($client['notes'] ?? ''),
        ];
    }

    $data = [
        'bill_to' => $bill_to,
        'items'   => $items,
        'total'   => (float)($invoice_data['total_amount'] ?? 0),
    ];

    
    // Try to load HTML from DB, else fallback to filesystem
        $invoice_html = $invoice_data['html'] ?? '';
        
        // ✅ IMPORTANT: keep ONLY the table HTML (prevents full-page duplication on regen)
        $invoice_table_html = extract_first_table_html($invoice_html);
        
        if (trim($invoice_table_html) === '') {
            $html_path = __DIR__ . "/invoices/{$invoice_number}.html";
            if (file_exists($html_path)) {
                $invoice_html = file_get_contents($html_path);
                $invoice_table_html = extract_first_table_html($invoice_html);
                file_put_contents('log_debug_status.txt', "✅ Loaded invoice HTML from file fallback for invoice {$invoice_number}\n", FILE_APPEND);
            }
        }

    if (trim($invoice_table_html) === '') {
        file_put_contents('log_debug_status.txt', "❌ Invoice HTML is empty for invoice {$invoice_number}. Falling back to regenerate basic table.\n", FILE_APPEND);
    
        // Reconstruct a simple fallback table if items exist
        if (!empty($items)) {
            $invoice_html = "<table class='invoice-table'><thead><tr>
                <th>Booking ID</th>
                <th>Car Type</th>
                <th>Pick-up</th>
                <th>Drop-off</th>
                <th>Booking Time</th>
                <th>Amount Paid</th>
                <th>Status</th>
            </tr></thead><tbody>";
            foreach ($items as $item) {
                $invoice_html .= "<tr>
                    <td>" . htmlspecialchars($item['Booking ID'] ?? '') . "</td>
                    <td>" . htmlspecialchars($item['Car Type'] ?? '') . "</td>
                    <td>" . htmlspecialchars($item['Pick-up'] ?? '') . "</td>
                    <td>" . htmlspecialchars($item['Drop-off'] ?? '') . "</td>
                    <td>" . htmlspecialchars($item['Booking Time'] ?? '') . "</td>
                    <td>" . htmlspecialchars($cur) . " " . number_format((float)($item['Amount Paid'] ?? 0), 2) . "</td>
                    <td>" . htmlspecialchars($item['Status'] ?? '') . "</td>
                </tr>";
            }
            $invoice_html .= "</tbody></table>";
            // ✅ already table-only here
            $invoice_table_html = $invoice_html;
        } else {
            throw new Exception("Invoice HTML and item data are both empty. Cannot generate invoice.");
        }
    }

    
    // ✅ Extract data and include template_invoice.php
    ob_start();
    // ✅ Always trust the DB value for due_date with time
    $invoice_data['due_date'] = $invoice_data['due_date'] ?? $invoice_row['due_date'];
    extract([
        'invoice_number'   => $invoice_data['invoice_number'],
        'date'             => $invoice_data['invoice_date'] ?? $invoice_data['created_at'],
        'due_date'         => $invoice_data['due_date'],
        'payment_link'     => $invoice_data['payment_link'],
        'status'           => $status,
        'items'            => $items,
        'invoice_total'    => $invoice_data['total_amount'],
        'data'             => $data,
        'invoice_html'     => $invoice_table_html,
        'payment_method'   => $payment_method,
        'invoice_data'     => $invoice_data, // Optional but useful
    ]);

    // 🔍 Ensure due_date contains date + time correctly
    $debug_due = $invoice_data['due_date'] ?? '(not set)';
    file_put_contents('debug_due_date.txt', "✅ DUE_DATE IN update_status.php: {$debug_due}\n", FILE_APPEND);

    include 'template_invoice.php';
    $html = ob_get_clean();
    if (empty($html)) {
    throw new Exception("❌ Generated HTML is empty after including template.");
    }

    // ✅ Always strip any old PAID watermark first (prevents "Paid watermark" showing on Unpaid)
    $html = strip_paid_watermark_blocks($html);
    
    // ✅ Add PAID watermark only when status is Paid
    if ($status === 'Paid') {
        $watermarkBlock = '<!--DB_PAID_WATERMARK-->'
            . '<div style="position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; color: rgba(0, 128, 0, 0.15); font-weight: bold; z-index: 999;">PAID</div>'
            . '<!--/DB_PAID_WATERMARK-->';
    
        if (stripos($html, '<body') !== false) {
            $html = preg_replace('/<body\b[^>]*>/i', '$0' . $watermarkBlock, $html, 1);
        } else {
            $html = $watermarkBlock . $html;
        }
    }
    
    // ✅ Ensure a clean Total Amount block exists (fixes "Total Amount disappears on Paid")
    $html = preg_replace('/<!--DB_TOTAL_AMOUNT_BLOCK-->.*?<!--\/DB_TOTAL_AMOUNT_BLOCK-->/is', '', $html);
    
    if (stripos($html, 'Total Amount') === false) {
        $displayCur   = htmlspecialchars((string)$cur, ENT_QUOTES, 'UTF-8');
        $totalNumber  = number_format((float)($invoice_data['total_amount'] ?? 0), 2);
        $totalColor   = ($status === 'Paid') ? 'green' : 'red';
    
        $totalBlock = '<!--DB_TOTAL_AMOUNT_BLOCK-->'
            . '<div style="margin-top: 14px; text-align: right; font-size: 14px;">'
            . '<strong>Total Amount:</strong> '
            . '<span style="color:' . $totalColor . '; font-weight: 700;">' . $displayCur . ' ' . $totalNumber . '</span>'
            . '</div>'
            . '<!--/DB_TOTAL_AMOUNT_BLOCK-->';
    
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $totalBlock . '</body>', $html);
        } else {
            $html .= $totalBlock;
        }
    }
    
    // ✅ Save final HTML
    file_put_contents($html_path, $html, LOCK_EX);
    
    // ✅ Keep DB html as "table-only" source for future regenerations
    $pdo->prepare("UPDATE invoices SET html = ? WHERE id = ?")->execute([$invoice_table_html, $invoice_id]);
    
    // ✅ Generate PDF only AFTER all modifications
    $dompdf = new Dompdf\Dompdf();
    
    // ✅ Set options BEFORE loadHtml/render (more reliable)
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('isHtml5ParserEnabled', true);
    
    $dompdf->loadHtml($html);
    
    // ✅ A4 Landscape
    $dompdf->setPaper('A4', 'landscape');
    
    $dompdf->render();
    
    $pdf_path = __DIR__ . "/invoices/{$invoice_number}.pdf";
    file_put_contents($pdf_path, $dompdf->output(), LOCK_EX);

    // ✅ EMAIL BLOCK with Template Support
    $subject = ($status === 'Paid')
        ? "Payment Received for Invoice {$invoice_number}"
        : "Invoice {$invoice_number} Marked as Unpaid";
    
    $due_date_formatted = '';
    if (!empty($due_date)) {
        $due_dt = new DateTime($due_date);
        $due_date_formatted = $due_dt->format('F j, Y');
        if ($due_dt->format('H:i') !== '00:00') {
            $due_date_formatted .= ' ' . $due_dt->format('g:i A');
        }
    }

    // ✅ Multi-currency safe total for email
    $emailCur = trim((string)($invoice_data['currency_display'] ?? ''));
    if ($emailCur === '') {
        $emailCur = trim((string)($invoice_data['currency_code'] ?? ''));
    }
    if ($emailCur === '') {
        $emailCur = 'CAD';
    }

    // ✅ Dynamic invoice URL (no hardcoding)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // If update_status.php is inside a subfolder, this gives: /accounting/invoice-generator
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    
    // invoices folder is alongside update_status.php in same project folder
    $invoice_url = $scheme . '://' . $host . $basePath . '/invoices/' . rawurlencode($invoice_number) . '.pdf';

    $replacements = [
        '{{client_name}}'    => $client_name,
        '{{invoice_number}}' => $invoice_number,
        '{{company_name}}'   => get_setting('company_name'),
        '{{total_amount}}'   => $emailCur . ' ' . number_format((float)($invoice_data['total_amount'] ?? 0), 2),
        '{{payment_link}}'   => $invoice_data['payment_link'] ?? '#',
        '{{due_date}}'       => $due_date_formatted,
        '{{invoice_url}}'    => $invoice_url
    ];
    
    // ✅ Load template DIRECTLY from DB (same source as manage-email-templates.php)
    // ✅ No hardcoded fallback. If template missing → throw error so you can fix the template mapping.
    $templateRow = fetch_email_template_by_slug($pdo, $notification_type);
    
    if (!$templateRow || trim((string)($templateRow['body'] ?? '')) === '') {
        throw new Exception("Email template not found for notification type: {$notification_type}. Please set it in Settings → Email Templates.");
    }
    
    // If your DB template has its own subject, use it (otherwise keep the subject already set above)
    $templateSubject = trim((string)($templateRow['subject'] ?? ''));
    if ($templateSubject !== '') {
        $subject = apply_template_replacements($templateSubject, $replacements);
    }
    
    // Apply placeholder replacements to template HTML body
    $body = apply_template_replacements((string)$templateRow['body'], $replacements);
    
    // Load CC/BCC from template (if columns exist)
    $ccList  = parse_email_list_db($templateRow['cc'] ?? '');
    $bccList = parse_email_list_db($templateRow['bcc'] ?? '');
    
    // Send email (apply CC/BCC + attachment)
    sendInvoiceEmail($client_email, $client_name, $subject, $body, $pdf_path, basename($pdf_path), $ccList, $bccList);
    file_put_contents('log_debug_status.txt', "CC: " . implode(',', $ccList) . " | BCC: " . implode(',', $bccList) . "\n", FILE_APPEND);
    file_put_contents('log_debug_status.txt', "Template: $notification_type\nBody:\n$body\n\n", FILE_APPEND);

    echo json_encode([
    'success' => true,
    'message' => 'Status updated and email sent successfully.'
    ]);


/*} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
*/
} catch (Exception $e) {

    // ✅ rollback invoice status/method/proof if we already updated but email/template failed later
    if (!empty($didUpdateInvoice) && !empty($invoice_original['id'])) {
        try {
            $pdo->prepare("UPDATE invoices SET status = ?, payment_method = ?, payment_proof = ? WHERE id = ?")
                ->execute([
                    $invoice_original['status'] ?? null,
                    $invoice_original['payment_method'] ?? null,
                    $invoice_original['payment_proof'] ?? null,
                    (int)$invoice_original['id']
                ]);
        } catch (Exception $ignore) {}
    }

    file_put_contents('log_debug_status.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

