<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'middleware.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

if (!isset($_POST['role_id'], $_POST['permission_id'], $_POST['assign'])) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit;
}

$userId = (int)$_SESSION['user_id'];

// ✅ Detect super admin (bypass)
$superAdminRoleId = 0;
$stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'super_admin' LIMIT 1");
$stmt->execute();
$superAdminRoleId = (int)($stmt->fetchColumn() ?: 0);

$roleIdSession   = (int)($_SESSION['role_id'] ?? 0);
$roleNameSession = strtolower(trim((string)($_SESSION['role_name'] ?? '')));

// If missing in session, fetch from DB
if ($roleIdSession <= 0 || $roleNameSession === '') {
  $st = $pdo->prepare("
    SELECT r.id AS role_id, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
    LIMIT 1
  ");
  $st->execute([$userId]);
  if ($ur = $st->fetch(PDO::FETCH_ASSOC)) {
    $roleIdSession   = (int)$ur['role_id'];
    $roleNameSession = strtolower((string)$ur['role_name']);
    $_SESSION['role_id'] = $roleIdSession;
    $_SESSION['role_name'] = (string)$ur['role_name'];
  }
}

$isSuperAdmin = ($superAdminRoleId > 0 && $roleIdSession === $superAdminRoleId) || ($roleNameSession === 'super_admin');

// ✅ Only super_admin OR users with manage_permissions can change permissions
if (!$isSuperAdmin && !has_permission('manage_permissions')) {
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

$roleId = (int)$_POST['role_id'];
$permissionId = (int)$_POST['permission_id'];
$assign = ((int)$_POST['assign'] === 1) ? 1 : 0;

if ($roleId <= 0 || $permissionId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid IDs']);
  exit;
}

// ✅ Only super_admin can grant/revoke manage_role_viewable
$permName = '';
$stp = $pdo->prepare("SELECT name FROM permissions WHERE id = ? LIMIT 1");
$stp->execute([$permissionId]);
$permName = (string)($stp->fetchColumn() ?: '');

if ($permName === 'manage_role_viewable' && !$isSuperAdmin) {
  echo json_encode([
    'success' => false,
    'message' => 'Only super_admin can change Role Viewable (Column Visibility) permission'
  ]);
  exit;
}

try {

// ✅ Only super_admin can modify the super_admin role (assign OR revoke)
    if ($superAdminRoleId > 0 && $roleId === $superAdminRoleId && !$isSuperAdmin) {
        echo json_encode(['success' => false, 'message' => 'Only super_admin can change super_admin role permissions']);
        exit;
    }
    
  if ($assign === 1) {
    // ✅ Insert only if it does not already exist (works even without UNIQUE index)
    $stmt = $pdo->prepare("
      INSERT INTO role_permissions (role_id, permission_id)
      SELECT ?, ?
      WHERE NOT EXISTS (
        SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?
      )
    ");
    $stmt->execute([$roleId, $permissionId, $roleId, $permissionId]);
} else {

    // ✅ Allow ONLY super_admin to revoke permissions from the super_admin ROLE
    //    (prevents admins/managers with manage_permissions from weakening super_admin)
    if ($superAdminRoleId > 0 && $roleId === $superAdminRoleId && !$isSuperAdmin) {
        echo json_encode(['success' => false, 'message' => 'Only super_admin can change super_admin role permissions']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
    $stmt->execute([$roleId, $permissionId]);
}

  // ✅ IMPORTANT: clear any cached permission maps so next reload reads DB
  unset($_SESSION['permissions']);
  unset($_SESSION['permission_cache']);
  unset($_SESSION['role_permissions']);

  echo json_encode(['success' => true]);
} catch (Throwable $e) { 
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
