<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

// ✅ Detect super admin (bypass permission gate)
$userId = (int)$_SESSION['user_id'];

$superAdminRoleId = 0;
$stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'super_admin' LIMIT 1");
$stmt->execute();
$superAdminRoleId = (int)($stmt->fetchColumn() ?: 0);

$roleId   = (int)($_SESSION['role_id'] ?? 0);
$roleName = strtolower(trim((string)($_SESSION['role_name'] ?? '')));

// If session role info missing, fetch from DB
if ($roleId <= 0 || $roleName === '') {
  $st = $pdo->prepare("
    SELECT r.id AS role_id, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
    LIMIT 1
  ");
  $st->execute([$userId]);
  if ($ur = $st->fetch(PDO::FETCH_ASSOC)) {
    $roleId   = (int)$ur['role_id'];
    $roleName = strtolower((string)$ur['role_name']);
    $_SESSION['role_id'] = $roleId;
    $_SESSION['role_name'] = (string)$ur['role_name'];
  }
}

$isSuperAdmin = ($superAdminRoleId > 0 && $roleId === $superAdminRoleId) || ($roleName === 'super_admin');

if (!$isSuperAdmin && !has_permission('manage_role_viewable')) {
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

$target = isset($_POST['target_role_id']) ? (int)$_POST['target_role_id'] : 0;
$viewer = isset($_POST['viewer_role_id']) ? (int)$_POST['viewer_role_id'] : 0;
$allow  = isset($_POST['allow']) ? (int)$_POST['allow'] : 0;
$allow = $allow ? 1 : 0;

if ($target <= 0 || $viewer <= 0) {
  echo json_encode(['success' => false, 'message' => 'Missing role ids']);
  exit;
}

// Super Admin should ALWAYS be able to see everything (hard safety)
if ($superAdminRoleId && $viewer === $superAdminRoleId) {
  $allow = 1;
}

try {
  if ($allow === 1) {
    $stmt = $pdo->prepare("
      INSERT IGNORE INTO role_column_visibility (target_role_id, viewer_role_id)
      VALUES (?, ?)
    ");
    $stmt->execute([$target, $viewer]);
  } else {
    $stmt = $pdo->prepare("
      DELETE FROM role_column_visibility
      WHERE target_role_id = ? AND viewer_role_id = ?
    ");
    $stmt->execute([$target, $viewer]);
  }

  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
