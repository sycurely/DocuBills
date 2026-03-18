<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'config.php';
require_once 'middleware.php'; // ✅ needed for has_permission()

// 🔐 Must be logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ✅ Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Client ID required']);
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];
$canViewAllClients = has_permission('view_all_clients');

// 🔐 Secure query: exclude deleted + enforce ownership unless view_all_clients
$sql = "
    SELECT *
    FROM clients
    WHERE id = :id
      AND deleted_at IS NULL
";

$params = [':id' => $id];

if (!$canViewAllClients) {
    $sql .= " AND created_by = :uid";
    $params[':uid'] = $currentUserId;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        echo json_encode($client);
    } else {
        // 404 for both "not found" and "not allowed" (privacy-safe)
        http_response_code(404);
        echo json_encode(['error' => 'Client not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
