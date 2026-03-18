<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
  echo "<p>User ID is missing.</p>";
  exit;
}

$userId = (int) $_GET['id'];

$stmt = $pdo->prepare("
  SELECT users.id, users.username, users.full_name, users.created_at, roles.name AS role_name
  FROM users
  LEFT JOIN roles ON users.role_id = roles.id
  WHERE users.id = ? AND users.deleted_at IS NULL
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo "<p>User not found.</p>";
  exit;
}
?>

<!-- ✅ View User Modal Content -->
<h2 class="modal-title">User Details</h2>
<p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
<p><strong>Email / Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong>Role:</strong> <?= ucwords(str_replace('_', ' ', $user['role_name'] ?? 'Unassigned')) ?></p>
<p><strong>Created At:</strong> <?= date('Y-m-d H:i A', strtotime($user['created_at'])) ?></p>

<div class="form-actions" style="text-align: right;">
  <button class="btn btn-secondary" onclick="closeModal('viewUserModal')">
    <i class="fas fa-times"></i> Close
  </button>
</div>
