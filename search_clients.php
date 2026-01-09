<?php
session_start();

require_once 'config.php';
require_once 'middleware.php';

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Must be logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

// Must be allowed to create invoice (same logic you already had)
if (!has_permission('create_invoice')) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

// Safety limit
if (function_exists('mb_strlen')) {
    if (mb_strlen($q) > 80) $q = mb_substr($q, 0, 80);
} else {
    if (strlen($q) > 80) $q = substr($q, 0, 80);
}

// Escape LIKE wildcards safely
function escape_like($str) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
}

$currentUserId = (int)$_SESSION['user_id'];
$canViewAll    = has_permission('view_all_clients');

// ✅ PREFIX ONLY: "a%" not "%a%"
$prefix = escape_like($q) . '%';

try {
    $sql = "
        SELECT 
            id,
            company_name,
            representative,
            phone,
            email,
            address
        FROM clients
        WHERE deleted_at IS NULL
          AND company_name LIKE :term ESCAPE '\\\\'
    ";

    $params = [':term' => $prefix];

    // 🔐 Only show own clients unless view_all_clients
    if (!$canViewAll) {
        $sql .= " AND created_by = :uid";
        $params[':uid'] = $currentUserId;
    }

    $sql .= "
        ORDER BY company_name ASC
        LIMIT 25
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    error_log('search_clients.php error: ' . $e->getMessage());
    echo json_encode([]);
}
