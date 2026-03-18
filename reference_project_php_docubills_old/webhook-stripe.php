<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

$stripeSecret = get_setting('stripe_secret');
\Stripe\Stripe::setApiKey($stripeSecret);

$payload = @file_get_contents("php://input");
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$endpointSecret = STRIPE_WEBHOOK_SECRET;

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
} catch(Exception $e) {
    http_response_code(400);
    exit('Invalid payload');
}

// Only process successful payment
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $invoiceNumber = $session->metadata->invoice_number ?? '';

    if ($invoiceNumber) {
        $stmt = $pdo->prepare("UPDATE invoices SET status = 'Paid' WHERE invoice_number = ?");
        $stmt->execute([$invoiceNumber]);
    }
}

http_response_code(200);
echo 'Webhook received';
