<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// 1. Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
  echo json_encode(['success' => false, 'message' => 'All fields are required.']);
  exit;
}

if ($newPassword !== $confirmPassword) {
  echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
  exit;
}

// 2. Fetch user password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
  echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
  exit;
}

// 3. Update password
$newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->execute([$newHashed, $userId]);

echo json_encode(['success' => true]);
exit;
?>
