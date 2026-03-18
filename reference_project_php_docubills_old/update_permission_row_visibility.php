<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

/* ✅ Only Super Admin can control who can see the “manage_role_viewable” row */
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT r.id, r.name
  FROM users u
  JOIN roles r ON r.id = u.role_id
  WHERE u.id = ?
  LIMIT 1
");
$stmt->execute([$userId]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

$myRoleName = strtolower(str_replace(' ', '_', trim((string)($me['name'] ?? ''))));
$isSuperAdmin = ($myRoleName === 'super_admin');

if (!$isSuperAdmin) {
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

$permissionId = isset($_POST['permission_id']) ? (int)$_POST['permission_id'] : 0;
$viewerRoleId = isset($_POST['viewer_role_id']) ? (int)$_POST['viewer_role_id'] : 0;
$allow        = isset($_POST['allow']) ? (int)$_POST['allow'] : 0;
$allow = $allow ? 1 : 0;

if ($permissionId <= 0 || $viewerRoleId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Missing ids']);
  exit;
}

/* ✅ Super Admin role must always be allowed */
$sa = $pdo->query("SELECT id FROM roles WHERE LOWER(REPLACE(name,' ','_'))='super_admin' LIMIT 1")->fetchColumn();
$superAdminRoleId = (int)($sa ?: 0);

if ($superAdminRoleId && $viewerRoleId === $superAdminRoleId) {
  $allow = 1;
}

try {
  if ($allow === 1) {
    $st = $pdo->prepare("
      INSERT IGNORE INTO permission_row_visibility (permission_id, viewer_role_id)
      VALUES (?, ?)
    ");
    $st->execute([$permissionId, $viewerRoleId]);
  } else {
    $st = $pdo->prepare("
      DELETE FROM permission_row_visibility
      WHERE permission_id = ? AND viewer_role_id = ?
    ");
    $st->execute([$permissionId, $viewerRoleId]);
  }

  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
