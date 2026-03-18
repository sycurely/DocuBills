<?php
// ─── update_expense_status.php ───────────────────────────────────────────
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';
require_once __DIR__ . '/mailer.php';

// 1) Permission check
if (!has_permission('change_expense_status')) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Permission denied']);
    exit;
}

// 2) Required params
$expense_id     = $_POST['expense_id']     ?? null;
$status         = $_POST['status']         ?? null;
$payment_method = $_POST['payment_method'] ?? null;

if (!$expense_id || !$status) {
    echo json_encode(['success'=>false,'error'=>'Missing parameters']);
    exit;
}

// 3) Handle optional file upload
$proof_path = null;
if (
    isset($_FILES['payment_proof']) &&
    $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK
) {
    $uploadDir = __DIR__ . '/uploads/expense_receipts/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext  = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
    $name = 'expense_'.$expense_id.'_'.time().'.'.$ext;
    $dest = $uploadDir . $name;

    if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dest)) {
        // store the web‐relative path
        $proof_path = 'uploads/expense_receipts/' . $name;
    }
}

try {
    // 4) Update expenses safely (never wipe existing proof/method unless provided)
    $set = ["status = ?"];
    $params = [$status];
    
    // only update payment_method if it was provided (Paid modal sends it)
    if ($payment_method !== null && trim((string)$payment_method) !== '') {
        $set[] = "payment_method = ?";
        $params[] = $payment_method;
    }
    
    // only update proof if a NEW file was uploaded
    if ($proof_path !== null) {
        $set[] = "payment_proof = ?";
        $params[] = $proof_path;
    }
    
    $params[] = $expense_id;
    
    $sql = "UPDATE expenses SET " . implode(", ", $set) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 5) SEND EMAIL when marking Paid OR Unpaid
    $statusNorm = strtolower(trim((string)$status));
    
    if (in_array($statusNorm, ['paid', 'unpaid'], true)) {
    
        // ✅ Fallback template names (won't break your existing Paid flow)
        $templateName = ($statusNorm === 'paid') ? 'Expense Paid' : 'Expense Unpaid';
    
        // ✅ Preferred notification-type slugs (use these in manage-email-templates.php)
        // If you want different slugs, change them here.
        $notifSlug = ($statusNorm === 'paid') ? 'expense_paid' : 'expense_unpaid';
    
        // 5a) Fetch template (prefer assigned_notification_type, fallback to template_name)
        $tplStmt = $pdo->prepare("
            SELECT 
              template_name,
              template_html,
              html_content,
              cc_emails,
              bcc_emails
            FROM email_templates
            WHERE deleted_at IS NULL
              AND (
                assigned_notification_type = :slug
                OR template_name = :tpl
              )
            ORDER BY
              CASE WHEN assigned_notification_type = :slug THEN 0 ELSE 1 END,
              id DESC
            LIMIT 1
        ");
        $tplStmt->execute([
            'slug' => $notifSlug,
            'tpl'  => $templateName
        ]);
        $tpl = $tplStmt->fetch(PDO::FETCH_ASSOC);
    
        // If template missing, don’t fail the status update — just log it
        if (!$tpl) {
            error_log("Expense {$statusNorm} email: template not found. Looked for slug='{$notifSlug}' OR template_name='{$templateName}'");
            echo json_encode(['success' => true, 'email_sent' => false, 'email_error' => 'Template not found']);
            exit;
        }
    
        $subject = (string)($tpl['template_name'] ?? '');
        // ✅ Prefer rendered HTML (html_content). template_html can be Unlayer JSON sometimes.
        $body = (string)($tpl['html_content'] ?? '');
        $alt  = (string)($tpl['template_html'] ?? '');
        
        $looksJson = function($s){
            $t = ltrim((string)$s);
            return $t !== '' && ($t[0] === '{' || $t[0] === '[');
        };
        
        if ($body === '' || $looksJson($body)) {
            if ($alt !== '' && !$looksJson($alt)) {
                $body = $alt;
            }
        }
    
        // ✅ CC/BCC comes ONLY from template settings (manage-email-templates.php)
        $cc_list  = parse_email_list($tpl['cc_emails'] ?? '', 10);
        $bcc_list = parse_email_list($tpl['bcc_emails'] ?? '', 10);
    
        // Store template cc/bcc into expense record (so “Payment Info” shows it)
        $email_cc  = $cc_list  ? implode(',', $cc_list)  : null;
        $email_bcc = $bcc_list ? implode(',', $bcc_list) : null;
    
        $pdo->prepare("UPDATE expenses SET email_cc = ?, email_bcc = ? WHERE id = ?")
            ->execute([$email_cc, $email_bcc, $expense_id]);
    
        // 5b) Find expense owner email (created_by)
        $ownerStmt = $pdo->prepare("
            SELECT u.email
            FROM expenses e
            LEFT JOIN users u ON u.id = e.created_by
            WHERE e.id = ?
            LIMIT 1
        ");
        $ownerStmt->execute([$expense_id]);
        $ownerEmail = trim((string)$ownerStmt->fetchColumn());
    
        if (!$ownerEmail || !filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("Expense {$statusNorm} email: invalid owner email for expense_id={$expense_id}. Email='{$ownerEmail}'");
            echo json_encode(['success' => true, 'email_sent' => false, 'email_error' => 'Owner email missing/invalid']);
            exit;
        }
    
        // 5b-2) Pull expense row for template variables
        $expStmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? LIMIT 1");
        $expStmt->execute([$expense_id]);
        $expenseRow = $expStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Recipient name (best-effort)
        $recipientName = 'there';
        if (!empty($expenseRow['created_by'])) {
            $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $uStmt->execute([$expenseRow['created_by']]);
            $uRow = $uStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
            $candidate = trim((string)($uRow['name'] ?? $uRow['full_name'] ?? $uRow['first_name'] ?? $uRow['username'] ?? ''));
            if ($candidate !== '') $recipientName = $candidate;
        }
        
        // Expense fields (support multiple possible column names)
        $expenseTitle = trim((string)($expenseRow['expense_title'] ?? $expenseRow['title'] ?? $expenseRow['expense_name'] ?? $expenseRow['name'] ?? ''));
        if ($expenseTitle === '') $expenseTitle = 'Expense';
        
        $expenseRef = trim((string)($expenseRow['expense_ref'] ?? $expenseRow['ref'] ?? $expenseRow['reference'] ?? $expenseRow['reference_no'] ?? ''));
        if ($expenseRef === '') $expenseRef = (string)$expense_id;
        
        $amountRaw = $expenseRow['expense_amount'] ?? $expenseRow['amount'] ?? $expenseRow['total_amount'] ?? $expenseRow['cost'] ?? 0;
        $amountNum = is_numeric($amountRaw) ? (float)$amountRaw : 0.0;
        
        // Currency symbol (best-effort)
        $currencySymbol = '';
        if (!empty($expenseRow['currency_symbol'])) $currencySymbol = (string)$expenseRow['currency_symbol'];
        elseif (!empty($expenseRow['currency'])) $currencySymbol = (string)$expenseRow['currency'];
        elseif (function_exists('get_setting')) $currencySymbol = (string)(get_setting('currency_symbol') ?? get_setting('currency') ?? '');
        if ($currencySymbol === '') $currencySymbol = '$';
        
        $expenseAmount = $currencySymbol . number_format($amountNum, 2);
        
        // Company name (best-effort)
        $companyName = function_exists('get_setting') ? (string)(get_setting('company_name') ?? '') : '';
        if ($companyName === '') $companyName = 'DocuBills';
        
        // Build "View Expense" URL (✅ change view-expense.php if your page name differs)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        $base   = rtrim($scheme . $host . dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
        $expenseDetailsUrl = $base . 'view-expense.php?id=' . urlencode((string)$expense_id);
        
        // ✅ Apply template variables (works for {{recipient_name}} etc.)
        $replacements = [
        'recipient_name'      => $recipientName,
        'expense_title'       => $expenseTitle,
        'expense_ref'         => $expenseRef,
        'expense_amount'      => $expenseAmount,
        'company_name'        => $companyName,
    
        // ✅ Your Unlayer button uses {{expense_url}}
        'expense_url'         => $expenseDetailsUrl,
    
        // (optional) keep this too in case you use it elsewhere
        'expense_details_url' => $expenseDetailsUrl,
        ];
        
        // ✅ Replace placeholders in subject/body
        if (function_exists('render_template_vars')) {
            $subject = render_template_vars($subject, $replacements);
            $body    = render_template_vars($body, $replacements);
        }
        
        // 5c) Send using your SMTP PHPMailer
        $ok = sendInvoiceEmail(
            $ownerEmail,
            '',
            $subject,
            $body,
            '',  // no attachment
            '',
            $cc_list,
            $bcc_list
        );
    
        if (!$ok) {
            error_log("Expense {$statusNorm} email FAILED for expense_id={$expense_id} TO={$ownerEmail}. Check email_debug.log and email_smtp_debug.log");
            echo json_encode(['success' => true, 'email_sent' => false, 'email_error' => 'PHPMailer failed (see logs)']);
            exit;
        }
    
        echo json_encode(['success' => true, 'email_sent' => true]);
        exit;
    }
    
    // 6) JSON success back to AJAX (covers statuses other than Paid/Unpaid)
    echo json_encode(['success' => true]);
    exit;

} catch (\Exception $e) {
    // log and report back
    error_log($e->getMessage());
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}