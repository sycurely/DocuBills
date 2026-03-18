<?php
require_once 'config.php';
require_once 'mailer.php';

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT template_name, html_content FROM email_templates WHERE id = ?");
$stmt->execute([$id]);
if (!$row = $stmt->fetch()) {
    header('Location: settings-email-templates-list.php?error=Invalid+ID');
    exit;
}

// Replace placeholders with demo data
$html = str_replace(
    ['{{client_name}}','{{invoice_number}}'],
    ['Test Client','INV-0001'],
    $row['html_content']
);

// Fetch admin email from settings table
$adminStmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'admin_email' LIMIT 1");
$adminStmt->execute();
$adminEmail = $adminStmt->fetchColumn() ?: 'admin@example.com';

// Send preview
$sent = sendInvoiceEmail(
    $adminEmail,
    'You',
    "[Preview] {$row['template_name']}",
    $html
);


// If it failed, log the PHPMailer ErrorInfo
if (!$sent) {
    error_log("Preview Email Error: " . (defined('PHPMailer\PHPMailer\PHPMailer::ERROR') 
        ? PHPMailer\PHPMailer\PHPMailer::ERROR 
        : 'Unknown error'));
    header('Location: settings-email-templates-list.php?error=Preview+failed+(check+logs)');
    exit;
}

header('Location: settings-email-templates-list.php?success=' . urlencode("Preview sent to {$adminEmail}"));
exit;
