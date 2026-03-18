<?php
require 'config.php';
session_start();

header('Content-Type: application/json');

$currentUserId = $_SESSION['user_id'] ?? 0;
$username = trim($_POST['username'] ?? '');

if ($username === '') {
    echo json_encode(['valid' => false, 'message' => 'Username cannot be empty.']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM users 
    WHERE LOWER(username) = LOWER(?) AND id <> ?
");
$stmt->execute([$username, $currentUserId]);
$exists = $stmt->fetchColumn() > 0;

echo json_encode([
    'valid' => !$exists,
    'message' => $exists ? 'That username is already taken.' : 'Username is available.'
]);
