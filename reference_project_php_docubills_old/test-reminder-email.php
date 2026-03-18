<?php
/**
 * Test Reminder Email Script
 * This script tests sending a reminder email directly
 * Access: https://www.docubills.com/test-reminder-email.php?cron_key=docubills_reminder_cron_2024
 */

if (!isset($_GET['cron_key']) || $_GET['cron_key'] !== 'docubills_reminder_cron_2024') {
    die('Access denied.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Testing Reminder Email ===\n\n";

// Get a test invoice
$stmt = $pdo->prepare("
    SELECT i.id, i.invoice_number, i.bill_to_name, i.bill_to_json, 
           i.total_amount, i.due_date, i.payment_link, i.status, i.client_id
    FROM invoices i
    WHERE i.deleted_at IS NULL
      AND i.status = 'Unpaid'
    LIMIT 1
");
$stmt->execute();
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die("No unpaid invoices found for testing.\n");
}

echo "Found test invoice: #{$invoice['invoice_number']}\n\n";

// Get client email from bill_to_json
$bill_to = json_decode($invoice['bill_to_json'] ?? '{}', true);
$client_email = $bill_to['Email'] ?? '';
$client_name = $bill_to['Contact Name'] ?? $invoice['bill_to_name'] ?? 'Client';

echo "Bill To JSON: " . substr($invoice['bill_to_json'] ?? '{}', 0, 200) . "\n";
echo "Extracted Email: '{$client_email}'\n";
echo "Extracted Name: '{$client_name}'\n\n";

if (empty($client_email)) {
    die("ERROR: No email found in bill_to_json. Keys: " . implode(', ', array_keys($bill_to ?? [])) . "\n");
}

// Get a test template
$stmt = $pdo->prepare("
    SELECT id, template_name, html_content, template_html
    FROM email_templates
    WHERE deleted_at IS NULL
    LIMIT 1
");
$stmt->execute();
$template = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$template) {
    die("No email templates found.\n");
}

echo "Using template: {$template['template_name']} (ID: {$template['id']})\n\n";

$template_html = $template['html_content'] ?? $template['template_html'] ?? '';

if (empty($template_html)) {
    die("Template has no HTML content.\n");
}

// Prepare replacements
$due_date_formatted = date('F j, Y', strtotime($invoice['due_date']));
$amount_due = 'CA$' . number_format((float)$invoice['total_amount'], 2);

$replacements = [
    'client_name' => $client_name,
    'invoice_number' => $invoice['invoice_number'],
    'amount_due' => $amount_due,
    'total_amount' => $amount_due,
    'due_date' => $due_date_formatted,
    'payment_link' => $invoice['payment_link'] ?? '',
    'company_name' => 'DocuBills',
];

$email_body = render_template_vars($template_html, $replacements);
$subject = "Test Reminder: Invoice {$invoice['invoice_number']} Payment Due";

echo "Subject: {$subject}\n";
echo "Email Body Length: " . strlen($email_body) . " bytes\n\n";

echo "Sending test email...\n";

$sent = sendInvoiceEmail(
    $client_email,
    $client_name,
    $subject,
    $email_body,
    '',
    '',
    [],
    []
);

if ($sent) {
    echo "\n✅ SUCCESS: Test email sent to {$client_email}\n";
    echo "Check your email inbox and spam folder.\n";
} else {
    echo "\n❌ FAILED: Email could not be sent.\n";
    echo "Check email_debug.log for details.\n";
}

echo "\n=== Test Complete ===\n";
