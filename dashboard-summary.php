<?php
require_once 'config.php';
header('Content-Type: application/json');

$response = [
    'total_revenue'   => 0,
    'total_deficit'   => 0,
    'top_clients'     => [],
    'recent_invoices' => []
];

try {
    // Total Revenue from Paid Invoices
    $response['total_revenue'] = (float) $pdo->query("SELECT SUM(total_amount) FROM invoices WHERE status = 'Paid' AND deleted_at IS NULL")->fetchColumn();

    // Total Deficit from Unpaid Invoices
    $response['total_deficit'] = (float) $pdo->query("SELECT SUM(total_amount) FROM invoices WHERE status = 'Unpaid' AND deleted_at IS NULL")->fetchColumn();

    // Recent 5 Invoices
    $stmt = $pdo->query("
    SELECT invoice_number, bill_to_name, total_amount, status, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at
        FROM invoices
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $response['recent_invoices'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load dashboard summary',
        'details' => $e->getMessage()
    ]);
}