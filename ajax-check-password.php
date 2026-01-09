<?php
session_start();
require_once 'config.php';

// Always return JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['valid' => false]);
  exit;
}

$userId = $_SESSION['user_id'];
$current = $_POST['current_password'] ?? '';

$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user && password_verify($current, $user['password'])) {
  echo json_encode(['valid' => true]);
} else {
  echo json_encode(['valid' => false]);
}

exit;
