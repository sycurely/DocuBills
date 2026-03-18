<?php
session_start();
$isSuperAdmin = ($_SESSION['user_role'] ?? '') === 'super_admin';
require_once 'middleware.php'; // ⬅️ must come before has_permission()
$canAssignRoles = $isSuperAdmin || has_permission('assign_roles');
require_once 'config.php';

if (!has_permission('edit_user')) {
  $_SESSION['access_denied'] = true;
  header("Location: access-denied.php");
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId     = (int) $_POST['user_id'];
  $full_name  = trim($_POST['full_name']);
  $username   = trim($_POST['username']);
  $email      = trim($_POST['email']);
  $role_id = (int) $_POST['role_id'];
    if (!$canAssignRoles) {
  // Revert role change if not allowed
      $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
      $stmt->execute([$userId]);
      $existing = $stmt->fetch();
      $role_id = $existing['role_id'] ?? $role_id;
    }
  $password   = $_POST['password'];

  try {
    // Check if username/email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetch()) {
      $_SESSION['error'] = "Username already exists.";
      header("Location: users.php");
      exit;
    }
    
    // Check if email is taken
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
      $_SESSION['error'] = "Email already exists.";
      header("Location: users.php");
      exit;
    }

    // Build update query
    if (!empty($password)) {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, role_id = ?, password = ? WHERE id = ?");
      $stmt->execute([$full_name, $username, $email, $role_id, $hashed, $userId]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, role_id = ? WHERE id = ?");
      $stmt->execute([$full_name, $username, $email, $role_id, $userId]);
    }

    $_SESSION['success'] = "User updated successfully.";
  } catch (Exception $e) {
    $_SESSION['error'] = "Error updating user: " . $e->getMessage();
  }

  header("Location: users.php");
  exit;
}

$_SESSION['error'] = "Invalid request.";
header("Location: users.php");
exit;
