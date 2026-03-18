<?php
require_once __DIR__ . '/mailer.php';

$testTo = 'marto9ine@gmail.com'; // put your own email here

$result = sendInvoiceEmail(
    $testTo,
    'Test User',
    'Test email from DocuBills mailer.php',
    '<p>This is a <strong>test email</strong> from DocuBills.</p>'
);

var_dump($result);
echo "<br>Check email_debug.log and email_smtp_debug.log for details.";
