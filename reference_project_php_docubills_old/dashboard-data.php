<?php
require_once 'config.php';
header('Content-Type: application/json');

// Check if we're requesting unpaid clients
if (isset($_GET['unpaid_clients']) && $_GET['unpaid_clients'] == 'true') {
    try {
        $stmt = $pdo->query("
            SELECT bill_to_name, COUNT(*) as count 
            FROM invoices 
            WHERE status = 'Unpaid' AND deleted_at IS NULL
            GROUP BY bill_to_name 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $top_unpaid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['top_unpaid' => $top_unpaid]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Check if we're requesting paid clients
if (isset($_GET['paid_clients']) && $_GET['paid_clients'] == 'true') {
    try {
        $stmt = $pdo->query("
            SELECT bill_to_name, COUNT(*) AS total
            FROM invoices
            WHERE deleted_at IS NULL
            GROUP BY bill_to_name
            ORDER BY total DESC
            LIMIT 5
        ");
        $top_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['top_clients' => $top_clients]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

$period = $_GET['period'] ?? 'daily';
$response = [
    'status' => ['paid' => 0, 'unpaid' => 0],
    'labels' => [],
    'paid_series' => [],
    'unpaid_series' => [],
    'total_revenue' => 0,
    'top_clients' => [],
    'recent_invoices' => []
];

try {
    // Count Paid/Unpaid (for doughnut)
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM invoices WHERE deleted_at IS NULL GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = strtolower($row['status']);
        $response['status'][$status] = (int)$row['count'];
    }

    // Time-based Grouped Bar Data
    switch ($period) {
        case 'monthly':
            $query = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS label,
                       SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                       SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid
                FROM invoices
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND deleted_at IS NULL
                GROUP BY label ORDER BY label
            ";
            break;
        case 'yearly':
            $query = "
                SELECT YEAR(created_at) AS label,
                       SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                       SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid
                FROM invoices
                WHERE deleted_at IS NULL
                GROUP BY label ORDER BY label DESC LIMIT 5
            ";
            break;
        case 'all':
            $query = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS label,
                       SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                       SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid
                FROM invoices
                WHERE deleted_at IS NULL
                GROUP BY label ORDER BY label
            ";
            break;
        case 'daily':
            default:
                $query = "
                    SELECT DATE(created_at) AS label,
                       COUNT(CASE WHEN status = 'Paid' THEN 1 END) AS paid,
                       COUNT(CASE WHEN status = 'Unpaid' THEN 1 END) AS unpaid
                FROM invoices
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  AND deleted_at IS NULL
                GROUP BY DATE(created_at)
                ORDER BY label
            ";

            file_put_contents('log_invoice_rows.json', json_encode($pdo->query("
                SELECT id, invoice_number, status, created_at
                FROM invoices
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  AND deleted_at IS NULL
            ")->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT));

            break;
    }

    $stmt = $pdo->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['labels'][] = $row['label'];
        $response['paid_series'][] = (int)$row['paid'];
        $response['unpaid_series'][] = (int)$row['unpaid'];
    }

    // Total revenue (paid)
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM invoices WHERE status = 'Paid' AND deleted_at IS NULL");
    $response['total_revenue'] = (float)($stmt->fetchColumn() ?? 0);

    // Top 5 clients
    $stmt = $pdo->query("
        SELECT bill_to_name, COUNT(*) AS total
        FROM invoices WHERE deleted_at IS NULL
        GROUP BY bill_to_name
        ORDER BY total DESC
        LIMIT 5
    ");
    $response['top_clients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent 5 invoices
    $stmt = $pdo->query("
        SELECT invoice_number, bill_to_name, total_amount, status, created_at
        FROM invoices
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $response['recent_invoices'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('log_dashboard_data.json', json_encode($response, JSON_PRETTY_PRINT));

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}