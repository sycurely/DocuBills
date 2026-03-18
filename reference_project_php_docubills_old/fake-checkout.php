<?php
require_once 'config.php';

$invoice = $_GET['invoice'] ?? '';

if ($invoice) {
    // Mark invoice as paid in DB
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'Paid' WHERE invoice_number = ?");
    $stmt->execute([$invoice]);

    // Redirect to thank you page
    header("Location: payment-success.php?invoice=" . urlencode($invoice));
    exit;
} else {
    echo "Invalid invoice.";
}

echo "Marked invoice $invoice as Paid!";
exit;