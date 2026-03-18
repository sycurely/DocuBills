<?php
// debug_permissions.php
session_start();

require_once 'config.php';
require_once 'middleware.php';

if (!isset($_SESSION['user_id'])) {
    exit('Not logged in.');
}

$userId = (int) $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("
    SELECT id, username, email, full_name, role, role_id
    FROM users
    WHERE id = ? AND deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('User not found.');
}

// Load all permission *names* for this user's role_id
$permissions = load_permissions_for((int)$user['role_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Permission Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        pre { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        h1 { margin-top: 0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; background: #4361ee; color: #fff; font-size: 12px; }
        .perm-list { columns: 3; -webkit-columns: 3; -moz-columns: 3; }
        .perm-item { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>Permission Debug</h1>

    <h2>User</h2>
    <pre><?php echo htmlspecialchars(print_r($user, true)); ?></pre>

    <h2>Role-based Permissions (permissions.name)</h2>
    <div class="perm-list">
        <?php foreach ($permissions as $p): ?>
            <div class="perm-item"><?php echo htmlspecialchars($p); ?></div>
        <?php endforeach; ?>
    </div>

    <h2>Quick Checks</h2>
    <pre>
view_dashboard: <?php var_export(has_permission('view_dashboard')); ?>

view_invoices: <?php var_export(has_permission('view_invoices')); ?>

view_clients: <?php var_export(has_permission('view_clients')); ?>

access_clients_tab: <?php var_export(has_permission('access_clients_tab')); ?>

view_expenses: <?php var_export(has_permission('view_expenses')); ?>

    </pre>
</body>
</html>
