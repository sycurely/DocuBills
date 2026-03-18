<?php
session_start();
require_once 'config.php';
require_once 'middleware.php';

if (!has_permission('manage_users')) {
  $_SESSION['error'] = "Unauthorized.";
  header('Location: users.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
  $userId = (int) $_POST['user_id'];

  if ($userId === (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header('Location: users.php');
    exit;
  }

  try {
    $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$userId]);
    $_SESSION['success'] = "User sent to Trash.";
  } catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
  }

  header('Location: users.php');
  exit;
}
