<?php
require_once 'config.php';

header('Content-Type: application/json');       // ✅ important

$field  = $_GET['field']  ?? '';
$value  = trim($_GET['value'] ?? '');
$userId = (int) ($_GET['user_id'] ?? 0);

if (!in_array($field, ['username', 'email']) || $value === '') {
    echo json_encode(['status' => 'invalid']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE $field = ? AND id != ?");
$stmt->execute([$value, $userId]);
$exists = $stmt->fetch();

echo json_encode([
    'status' => $exists ? 'taken' : 'available',
    'field'  => $field
]);
