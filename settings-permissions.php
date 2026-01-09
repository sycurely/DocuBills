<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'settings';
$activeTab = 'permissions';
require_once 'config.php';
require_once 'middleware.php';

$canManageRoleViewable = false; // ✅ will be checked from DB AFTER we know role id

ob_start(); // ✅ this is required before output 
require 'styles.php';

$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
// ✅ Column visibility: who can see which role column
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

// Try to get viewer role from session, fallback to DB
$currentUserRoleId   = (int)($_SESSION['role_id'] ?? 0);
$currentUserRoleName = (string)($_SESSION['role_name'] ?? '');

if ($currentUserRoleId <= 0 || $currentUserRoleName === '') {
  // If your users table name/column differs, adjust here
  $stmt = $pdo->prepare("
    SELECT r.id AS role_id, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
    LIMIT 1
  ");
  $stmt->execute([$currentUserId]);
  $ur = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($ur) {
    $currentUserRoleId = (int)$ur['role_id'];
    $currentUserRoleName = (string)$ur['role_name'];
    $_SESSION['role_id'] = $currentUserRoleId;
    $_SESSION['role_name'] = $currentUserRoleName;
  }
}

// Detect super_admin role id
$superAdminRoleId = 0;
foreach ($roles as $r) {
  if ($r['name'] === 'super_admin') {
    $superAdminRoleId = (int)$r['id'];
    break;
  }
}
$isSuperAdmin = ($superAdminRoleId > 0 && $currentUserRoleId === $superAdminRoleId) || (strtolower($currentUserRoleName) === 'super_admin');

// ✅ IMPORTANT: check manage_role_viewable from DB (not cached session)
// so Super Admin dropdown changes take effect instantly on next reload.
if ($isSuperAdmin) {
  $canManageRoleViewable = true;
} else {
  $stmt = $pdo->prepare("
    SELECT 1
    FROM role_permissions rp
    JOIN permissions p ON p.id = rp.permission_id
    WHERE rp.role_id = ?
      AND p.name = 'manage_role_viewable'
    LIMIT 1
  ");
  $stmt->execute([$currentUserRoleId]);
  $canManageRoleViewable = (bool)$stmt->fetchColumn();
}

$canManagePermissions = has_permission('manage_permissions');

// ✅ Allow access if user can manage permissions OR can manage role-viewable OR is super admin
if (!$isSuperAdmin && !$canManagePermissions && !$canManageRoleViewable) {
  $_SESSION['access_denied'] = true;
  header('Location: access-denied.php');
  exit;
}

// Load visibility map from DB
$roleColumnVisibility = []; // [target_role_id][viewer_role_id] = true
try {
  $stmt = $pdo->query("SELECT target_role_id, viewer_role_id FROM role_column_visibility");
  foreach ($stmt as $row) {
    $t = (int)$row['target_role_id'];
    $v = (int)$row['viewer_role_id'];
    $roleColumnVisibility[$t][$v] = true;
  }
} catch (Throwable $e) {
  // If table missing, fallback to old behavior (show all)
  $roleColumnVisibility = [];
}

// Build list of roles whose COLUMNS are visible to the current viewer
$visibleRoles = [];
foreach ($roles as $r) {
  $targetId = (int)$r['id'];

  if ($isSuperAdmin) {
    $visibleRoles[] = $r;
    continue;
  }

  // No config => show all (backwards compatible)
  if (empty($roleColumnVisibility) || !isset($roleColumnVisibility[$targetId])) {
    $visibleRoles[] = $r;
    continue;
  }

  // Allowed => show column
  if (isset($roleColumnVisibility[$targetId][$currentUserRoleId])) {
    $visibleRoles[] = $r;
  }
}

$permissionGroups = [
    'Invoices' => [
        'create_invoice', 'delete_invoice', 'edit_invoice', 'save_invoice', 'view_invoices', 
        'mark_invoice_paid', 'download_invoice_pdf', 'email_invoice', 'restore_invoices', 
        'view_invoice_payment_info', 'delete_forever', 'view_invoice_history', 'view_invoice_logs',
        'add_invoice_field', 'show_due_date', 'show_due_time', 
        'show_invoice_date', 'show_invoice_time', 'show_invoice_checkboxes',
        'toggle_bank_details',      // Banking Details Checkbox
        'manage_recurring_invoices' // Recurring Toggle / Manage (belongs to Invoices)
      ],

    'Clients' => [
      'access_clients_tab',
      'view_clients',
      'view_all_clients',   // 👈 NEW: View all users' clients
      'add_client',
      'edit_client',
      'delete_client',
      'restore_clients',
      'undo_recent_client',
      'undo_all_clients',
      'export_clients',
      'search_clients'
    ],

    'Expenses' => [
        'access_expenses_tab',
        'view_expenses',          // View own expenses
        'view_all_expenses',      // 🔑 NEW: View all users' expenses
        'add_expense',
        'edit_expense',
        'delete_expense',
        'undo_recent_expense',
        'undo_all_expenses',
        'change_expense_status',
        'search_expenses',
        'export_expenses'
    ],
    
    'Expenses Trash Bin' => [
      'view_expenses_trashbin',       // View own trash only
      'view_all_expenses_trashbin',   // 🔑 NEW: View all users' trash
      'restore_expenses',
      'delete_expense_forever'
    ],

    'Settings' => [
      'update_basic_settings',
      'access_basic_settings',
      'assign_roles',
      'manage_users_page',
      'suspend_users',
      'edit_user',
      'manage_permissions',

      // 🔐 Payment Settings (page + sub-permissions)
      'manage_payment_methods',   // can access the Payment Settings page
      'manage_card_payments',     // can manage Stripe / Square / Test Mode
      'manage_bank_details',      // can manage Banking Details block

      'manage_reminder_settings', // Reminder Settings (Email Reminders cadence)

      'access_email_templates_page',
      'add_email_template',
      'edit_email_template',
      'delete_email_template',
      
      'manage_notification_categories' // ✅ NEW

    ],

    'Special Settings' => [
          'manage_role_viewable'
    ],

  'Trash Bin' => ['access_trashbin', 'view_all_trash', 'restore_deleted_items'],
  'Email & Support' => ['access_support'],
  'Dashboard & Reports' => ['view_dashboard', 'access_reports'],
  
  'Login Logs' => [
    'view_login_logs',
    'terminate_sessions',
    'terminate_own_session'
  ],
];

// ✅ HARD-HIDE the entire Special Settings group from unauthorized viewers
if (!$isSuperAdmin && !$canManageRoleViewable) {
  unset($permissionGroups['Special Settings']);
}

$rolePermissions = [];
$permissions = $pdo->query("SELECT * FROM permissions")->fetchAll(PDO::FETCH_ASSOC);


// Sort alphabetically by name to make rendering predictable
usort($permissions, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// ✅ Permission id for manage_role_viewable (Role Viewable)
$roleViewablePermId = 0;
foreach ($permissions as $p) {
  if ($p['name'] === 'manage_role_viewable') {
    $roleViewablePermId = (int)$p['id'];
    break;
  }
}

$recommendedPermissions = [
  'super_admin' => array_column($permissions, 'id'), // ✅ All current permission IDs from DB

  'admin' => array_filter($permissions, function($p) {
    return in_array($p['name'], [
      'view_dashboard',
      'create_invoice',
      'manage_recurring_invoices',
      'view_invoices',
      'edit_invoice',
      'mark_invoice_paid',
      'download_invoice_pdf',
      'email_invoice',
      'view_invoice_history',
      'view_invoice_logs',
      'view_invoice_payment_info',

      'add_client',
      'edit_client',
      'delete_client',
      'view_clients',

      'access_expenses_tab',
      'view_expenses',
      'add_expense',
      'edit_expense',
      'delete_expense',
      'undo_recent_expense',
      'undo_all_expenses',
      'change_expense_status',
      'view_expense_details',
      'search_expenses',
      'export_expenses',

      'assign_roles',
      'edit_user',
      'manage_users_page',

      'add_email_template',
      'edit_email_template',
      'delete_email_template',
      'access_email_templates_page',
      'manage_notification_categories',

      'access_basic_settings',
      'update_basic_settings',
      'manage_payment_methods',
      'manage_card_payments',
      'manage_bank_details',
      'manage_reminder_settings',

      'access_history',
      'access_reports'
    ]);
  }, ARRAY_FILTER_USE_BOTH),

  'manager' => array_filter($permissions, function($p) {
    return in_array($p['name'], [
      'view_dashboard',
      'create_invoice',
      'view_invoices',
      'view_invoice_history',
      'view_invoice_payment_info',
      'view_clients',
      'view_expenses',
      'view_expenses_trashbin',
      'access_reports',
      'access_history'
    ]);
  }, ARRAY_FILTER_USE_BOTH),

  'assistant' => array_filter($permissions, function($p) {
    return in_array($p['name'], [
      'view_dashboard',
      'view_invoices',
      'view_invoice_history',
      'view_clients',
      'view_expenses'
    ]);
  }, ARRAY_FILTER_USE_BOTH),

  'viewer' => array_filter($permissions, function($p) {
    return in_array($p['name'], [
      'view_dashboard'
    ]);
  }, ARRAY_FILTER_USE_BOTH)
];

$stmt = $pdo->query("SELECT role_id, permission_id FROM role_permissions");
foreach ($stmt as $row) {
    $rolePermissions[$row['role_id']][$row['permission_id']] = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->exec("DELETE FROM role_permissions");
    foreach ($_POST['permissions'] ?? [] as $roleId => $permList) {
        foreach ($permList as $permId => $on) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$roleId, $permId]);
        }
    }
    $success = "Permissions updated successfully!";
}

// ✅ REFRESH permission map after save
$rolePermissions = [];
$stmt = $pdo->query("SELECT role_id, permission_id FROM role_permissions");
foreach ($stmt as $row) {
    $rolePermissions[$row['role_id']][$row['permission_id']] = true;
}
?>

<?php
function get_permission_label($key) {
  $map = [
    // Invoices
    'create_invoice' => 'Create Invoice',
    'edit_invoice' => 'Edit Invoice',
    'delete_invoice' => 'Delete Invoice',
    'view_invoices' => 'View Invoices',
    'view_invoice_history' => 'Invoice History',
    'view_invoice_logs' => 'All Invoice Logs',
    'add_invoice_field' => 'Add Field Button',
    'show_due_date' => 'Due Date',
    'show_due_time' => 'Due Time',
    'show_invoice_date' => 'Invoice Date',
    'show_invoice_time' => 'Invoice Time',
    'show_invoice_checkboxes' => 'Row Checkboxes',
    'toggle_bank_details'     => 'Banking Details Checkbox', // 👈 NEW
    'mark_invoice_paid' => 'Invoice Status',
    'download_invoice_pdf' => 'Download PDF',
    'email_invoice' => 'Email Invoice',
    'view_invoice_details' => 'Invoice Details',
    'restore_invoices' => 'Restore Invoices',
    'view_invoice_payment_info' => 'Payment Info',
    'delete_forever' => 'Delete Forever',

    // Clients
    'access_clients_tab' => 'Access Clients Tab',
    'view_clients' => 'View Clients',
    'view_all_clients' => 'View All Clients',  // 👈 NEW label
    'add_client' => 'Add Client',
    'edit_client' => 'Edit Client',
    'delete_client' => 'Delete Client',
    'restore_clients' => 'Restore Clients',
    'undo_recent_client' => 'Undo Recent Delete',
    'undo_all_clients' => 'Undo All Deletes',
    'export_clients' => 'Export to Excel',
    'search_clients' => 'Search Clients',

    //Expenses
    'access_expenses_tab'   => 'Access Expenses Tab',
    'view_expenses'         => 'View Expense Details',
    'view_all_expenses'     => 'View All Expenses',          // 🔑 NEW
    'undo_recent_expense'   => 'Undo Recent Delete',
    'undo_all_expenses'     => 'Undo All Deletes',
    'change_expense_status' => 'Change Status',
    'search_expenses'       => 'Search',
    'export_expenses'       => 'Export to Excel',

    // Expenses Trashbin
    'view_expenses_trashbin'      => 'Expenses Trashbin',
    'view_all_expenses_trashbin'  => 'View All Expenses Trash', // 🔑 NEW
    'restore_expenses'            => 'Restore Expenses',
    'delete_expense_forever'      => 'Delete Expenses Forever',

    // Recurring
    'manage_recurring_invoices'   => 'Recurring Toggle / Manage',

    // Settings
    'update_basic_settings'   => 'Edit Basic Settings',
    'access_basic_settings'   => 'View Basic Settings',
    'assign_roles'            => 'Assign Roles',
    'manage_users_page'       => 'Users Page',
    'suspend_users'           => 'Suspend / Unsuspend Users',
    'edit_user'               => 'Edit User',
    'manage_permissions'      => 'Manage Permissions',

    'manage_payment_methods'  => 'Payment Settings (Page Access)',
    'manage_card_payments'    => 'Card Payments (Stripe / Square)',
    'manage_bank_details'     => 'Banking Details',
    
    'manage_reminder_settings' => 'Reminder Settings',

    'access_email_templates_page' => 'Email Templates Page',
    'add_email_template'      => 'Create Template',
    'edit_email_template'     => 'Edit Template',
    'delete_email_template'   => 'Delete Template',
    
    'manage_notification_categories' => 'Email Template Categories (Add/Edit)',

    // Special Settings
    
    'manage_role_viewable' => 'Role Viewable (Column Visibility)',

    // Login Logs
    'view_login_logs' => 'View Login Logs',
    'terminate_sessions' => 'Terminate Any Session',
    'terminate_own_session' => 'Terminate Own Session',

    // Trash
    'access_trashbin' => 'Main Trashbin',
    'view_all_trash' => 'View All Trash',
    'restore_deleted_items' => 'Restore Deleted Items',

    // Dashboard / Misc
    'view_dashboard' => 'View Dashboard',
    'access_reports' => 'Reports',
    'access_support' => 'Help & Support',
    'manage_roles' => 'Manage Roles',
    'manage_users_page' => 'Users Page',
    'access_history' => 'Invoice History',
    
  ];

  return $map[$key] ?? ucwords(str_replace('_', ' ', $key));
}

function get_permission_icon($key) {
  $icons = [
    // 🔵 INVOICES
    'create_invoice' => '<i class="fas fa-plus-circle" style="color:#4cc9f0;"></i>',
    'edit_invoice' => '<i class="fas fa-edit" style="color:#4895ef;"></i>',
    'delete_invoice' => '<i class="fas fa-trash" style="color:#f72585;"></i>',
    'view_invoices' => '<i class="fas fa-file-invoice" style="color:#3f37c9;"></i>',
    'save_invoice' => '<i class="fas fa-save" style="color:#6a0dad;"></i>',
    'mark_invoice_paid' => '<i class="fas fa-check-circle" style="color:#4cc9f0;"></i>',
    'download_invoice_pdf' => '<i class="fas fa-download" style="color:#6a0dad;"></i>',
    'email_invoice' => '<i class="fas fa-envelope" style="color:#f8961e;"></i>',
    'view_invoice_details' => '<i class="fas fa-info-circle" style="color:#4895ef;"></i>',
    'restore_invoices' => '<i class="fas fa-undo" style="color:#4cc9f0;"></i>',
    'view_invoice_payment_info' => '<i class="fas fa-money-check-alt" style="color:#3f37c9;"></i>',
    'delete_forever' => '<i class="fas fa-times-circle" style="color:#f72585;"></i>',
    'view_invoice_history' => '<i class="fas fa-history" style="color:#4361ee;"></i>',
    'view_invoice_logs' => '<i class="fas fa-database" style="color:#f8961e;"></i>',
    'add_invoice_field' => '<i class="fas fa-plus-square" style="color:#6a0dad;"></i>',
    'show_due_date' => '<i class="fas fa-calendar-alt" style="color:#4895ef;"></i>',
    'show_due_time' => '<i class="fas fa-clock" style="color:#4cc9f0;"></i>',
    'show_invoice_date' => '<i class="fas fa-calendar-check" style="color:#3f37c9;"></i>',
    'show_invoice_time' => '<i class="fas fa-clock" style="color:#6a0dad;"></i>',
    'show_invoice_checkboxes' => '<i class="fas fa-check-square" style="color:#f8961e;"></i>',
    'toggle_bank_details'     => '<i class="fas fa-university" style="color:#2ca58d;"></i>', // 👈 NEW
    'access_history' => '<i class="fas fa-history" style="color:#4361ee;"></i>',

    // 🟢 CLIENTS
    'access_clients_tab' => '<i class="fas fa-users" style="color:#008080;"></i>',
    'view_clients' => '<i class="fas fa-eye" style="color:#4895ef;"></i>',
    'view_all_clients' => '<i class="fas fa-eye-slash" style="color:#f8961e;"></i>', // 👈 NEW icon
    'add_client' => '<i class="fas fa-user-plus" style="color:#4cc9f0;"></i>',
    'edit_client' => '<i class="fas fa-edit" style="color:#4895ef;"></i>',
    'delete_client' => '<i class="fas fa-user-times" style="color:#f72585;"></i>',
    'restore_clients' => '<i class="fas fa-undo" style="color:#4cc9f0;"></i>',
    'undo_recent_client' => '<i class="fas fa-undo" style="color:#4cc9f0;"></i>',
    'undo_all_clients' => '<i class="fas fa-history" style="color:#4895ef;"></i>',
    'export_clients' => '<i class="fas fa-file-export" style="color:#f8961e;"></i>',
    'search_clients' => '<i class="fas fa-search" style="color:#3f37c9;"></i>',

    // 🟠 EXPENSES
    'access_expenses_tab'   => '<i class="fas fa-wallet" style="color:#4cc9f0;"></i>',
    'view_expenses'         => '<i class="fas fa-eye" style="color:#4cc9f0;"></i>',
    'view_all_expenses'     => '<i class="fas fa-eye-slash" style="color:#f8961e;"></i>', // 🔑 NEW
    'add_expense'           => '<i class="fas fa-plus-circle" style="color:#4895ef;"></i>',
    'edit_expense'          => '<i class="fas fa-edit" style="color:#f4a261;"></i>',
    'delete_expense'        => '<i class="fas fa-trash-alt" style="color:#f72585;"></i>',
    'undo_recent_expense'   => '<i class="fas fa-undo" style="color:#4cc9f0;"></i>',
    'undo_all_expenses'     => '<i class="fas fa-history" style="color:#4895ef;"></i>',
    'change_expense_status' => '<i class="fas fa-exchange-alt" style="color:#f8961e;"></i>',
    'view_expense_details'  => '<i class="fas fa-info-circle" style="color:#4895ef;"></i>',
    'search_expenses'       => '<i class="fas fa-search" style="color:#3f37c9;"></i>',
    'export_expenses'       => '<i class="fas fa-file-export" style="color:#f8961e;"></i>',

    // 🟠 EXPENSES TRASHBIN
    'view_expenses_trashbin'      => '<i class="fas fa-trash-alt" style="color:#f8961e;"></i>',
    'view_all_expenses_trashbin'  => '<i class="fas fa-eye" style="color:#f8961e;"></i>', // 🔑 NEW
    'restore_expenses'            => '<i class="fas fa-undo" style="color:#4cc9f0;"></i>',
    'delete_expense_forever'      => '<i class="fas fa-times-circle" style="color:#f72585;"></i>',

    // 🔁 RECURRING
    'manage_recurring_invoices' => '<i class="fas fa-sync" style="color:#6a0dad;"></i>',

    // ⚙️ SETTINGS
    'update_basic_settings'   => '<i class="fas fa-edit" style="color:#4895ef;"></i>',
    'access_basic_settings'   => '<i class="fas fa-eye" style="color:#3f37c9;"></i>',
    'assign_roles'            => '<i class="fas fa-user-tag" style="color:#6a0dad;"></i>',
    'manage_users_page'       => '<i class="fas fa-users-cog" style="color:#3f37c9;"></i>',
    'suspend_users'           => '<i class="fas fa-user-slash text-danger"></i>',
    'edit_user'               => '<i class="fas fa-user-edit" style="color:#4895ef;"></i>',
    'manage_permissions'      => '<i class="fas fa-key" style="color:#dc3545;"></i>',

    'manage_reminder_settings' => '<i class="fas fa-bell" style="color:#4cc9f0;"></i>',

    'manage_payment_methods'  => '<i class="fas fa-sliders-h" style="color:#4cc9f0;"></i>',
    'manage_card_payments'    => '<i class="fas fa-credit-card" style="color:#4895ef;"></i>',
    'manage_bank_details'     => '<i class="fas fa-university" style="color:#2ca58d;"></i>',

    'access_email_templates_page' => '<i class="fas fa-layer-group" style="color:#6a0dad;"></i>',
    'add_email_template'      => '<i class="fas fa-plus" style="color:#4cc9f0;"></i>',
    'edit_email_template'     => '<i class="fas fa-edit" style="color:#4895ef;"></i>',
    'delete_email_template'   => '<i class="fas fa-trash" style="color:#f72585;"></i>',
    
    'manage_notification_categories' => '<i class="fas fa-tags" style="color:#4895ef;"></i>',

    // Special Settings
    
    'manage_role_viewable' => '<i class="fas fa-table-columns" style="color:#4895ef;"></i>',

    // Login Logs
    'view_login_logs' => '<i class="fas fa-list-alt" style="color:#4361ee;"></i>',
    'terminate_sessions' => '<i class="fas fa-power-off" style="color:#f72585;"></i>',
    'terminate_own_session' => '<i class="fas fa-user-slash" style="color:#f8961e;"></i>',

    // 🗑 TRASH BIN
    'access_trashbin' => '<i class="fas fa-trash-restore" style="color:#f8961e;"></i>',
    'view_all_trash' => '<i class="fas fa-eye" style="color:#f94144;"></i>',
    'restore_deleted_items' => '<i class="fas fa-recycle" style="color:#4cc9f0;"></i>',

    // 📊 DASHBOARD / MISC
    'view_dashboard' => '<i class="fas fa-chart-line" style="color:#4361ee;"></i>',
    'access_reports' => '<i class="fas fa-chart-pie" style="color:#4895ef;"></i>',
    'access_support' => '<i class="fas fa-question-circle" style="color:#17a2b8;"></i>',
  ];

  return $icons[$key] ?? ''; // ❌ No default checkmark anymore — just blank if not found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Role-Based Permissions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --header-height: 70px;
      --sidebar-width: 250px;
      --transition: all 0.3s ease;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }

    body {
      background: var(--body-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }

    .page-actions {
      display: flex;
      gap: 15px;
    }

    .btn {
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
      box-shadow: var(--shadow-hover);
    }

    .alert {
      padding: 1rem;
      border-radius: var(--radius);
      margin-bottom: 1.5rem;
    }

    .alert-success {
      background: rgba(76, 201, 240, 0.2);
      border: 1px solid var(--success);
      color: var(--success);
    }

    .table-container {
      overflow-x: auto;
      margin-top: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: var(--radius);
      overflow: hidden;
      background: var(--card-bg);
    }

    th, td {
      padding: 1rem;
      text-align: center;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      font-size: 1rem;
    }

    thead th {
      position: sticky;
      top: 0;
      z-index: 2;
      background: rgba(67, 97, 238, 0.08);
      color: var(--primary);
      border-bottom: 1px solid var(--border);
      box-shadow: inset 0 -1px 0 var(--border);
    }


    td input[type="checkbox"] {
      transform: scale(1.3);
      cursor: pointer;
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }
    
    .alert-success {
    animation: fadeSlideIn 0.6s ease-out;
    }
    
    @keyframes fadeSlideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .group-header:hover {
      background: rgba(67, 97, 238, 0.12);
    }
    
    .group-header .toggle-icon {
      transition: transform 0.2s ease;
    }
    
    .group-header.collapsed .toggle-icon { 
        transform: rotate(-90deg); }

    /* Prevent the table header from collapsing when all rows are hidden */
    .table-container table {
      table-layout: fixed;
    }
    
    .table-container {
      min-height: 200px;
      position: relative;
    }
    
    .table-container:after {
      content: '';
      display: block;
      height: 40px;
    }

    .padding-row td {
      height: 40px;
      background: transparent;
      pointer-events: none;
      border: none;
    }
    
    .th-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
    }
    
    .th-title {
      font-weight: 600;
      color: var(--primary);
    }
    
    .th-actions {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      justify-content: center;
    }
    
    /* Role column visibility dropdown */
    .cv-block { width: 100%; margin-top: 6px; }
    .cv-label { font-size: 11px; color: var(--gray); margin-bottom: 6px; }
    
    .cv-toggle {
      width: 100%;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 7px 10px;
      font-size: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    
    .cv-toggle i { font-size: 11px; color: var(--gray); }
    
    .cv-menu {
      display: none;
      position: fixed; /* IMPORTANT: prevents clipping inside the table */
      z-index: 99999;
      width: 230px;
      max-height: 240px;
      overflow: auto;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: var(--shadow-hover);
      padding: 8px;
    }
    
    .cv-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 7px 8px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 12px;
      color: var(--dark);
      user-select: none;
    }
    
    .cv-item:hover { background: rgba(67, 97, 238, 0.06); }
    .cv-item input { transform: scale(1.1); cursor: pointer; }
    .cv-note { margin-left: auto; font-style: normal; font-size: 10px; color: var(--gray); }

    /* Permission visibility dropdown (manage_role_viewable) */
    .pv-block { width: 100%; margin-top: 6px; }
    
    .pv-toggle {
      width: 100%;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 7px 10px;
      font-size: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    .pv-toggle i { font-size: 11px; color: var(--gray); }
    
    .pv-menu {
      display: none;
      position: fixed;
      z-index: 99999;
      width: 230px;
      max-height: 240px;
      overflow: auto;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: var(--shadow-hover);
      padding: 8px;
    }
    
    .pv-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 7px 8px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 12px;
      color: var(--dark);
      user-select: none;
    }
    .pv-item:hover { background: rgba(67, 97, 238, 0.06); }
    .pv-item input { transform: scale(1.1); cursor: pointer; }
    .pv-note { margin-left: auto; font-style: normal; font-size: 10px; color: var(--gray); }
    .pv-note { display:inline-flex; align-items:center; gap:6px; color: var(--gray); font-size: 12px; }
  </style>
</head>
<body>
<div class="app-container">
  <?php require 'sidebar.php'; ?>

  <div class="main-content">
    <?php require 'header.php'; ?>

    <div class="page-wrapper">
      <div class="page-header">
        <h1 class="page-title">Role-Based Permissions</h1>
      </div>
      
      <div class="page-actions">
          <button type="button" class="btn btn-primary" id="expandAll">
            <i class="fas fa-plus"></i> Expand All
          </button>
          <button type="button" class="btn btn-secondary" id="collapseAll">
            <i class="fas fa-minus"></i> Collapse All
          </button>
        </div>

      <div class="page-body">
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form id="permissionsForm" method="POST">
          <div class="table-container">
            <table class="table table-bordered">
              <thead>
                  <tr>
                    <th>Permission</th>
                
                    <?php foreach ($visibleRoles as $role): ?>
                      <th>
                        <div class="th-wrapper">
                          <div class="th-title"><?= ucwords(str_replace('_', ' ', $role['name'])) ?></div>
                
                          <div class="th-actions">
                            <button type="button" class="btn btn-sm btn-primary select-all"
                              data-role="<?= $role['id'] ?>" <?= (!$canManagePermissions && !$isSuperAdmin) ? 'disabled' : '' ?>>
                              All
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-secondary select-recommended"
                              data-role="<?= $role['id'] ?>" data-role-name="<?= $role['name'] ?>"
                              <?= (!$canManagePermissions && !$isSuperAdmin) ? 'disabled' : '' ?>>
                              Recommended
                            </button>
                          </div>
                
                          <?php if ($isSuperAdmin || $canManageRoleViewable): ?>
                            <div class="cv-block">
                              <div class="cv-label">Role Viewable</div>
                
                              <?php
                                $targetRoleId = (int)$role['id'];
                
                                $allowedCount = 0;
                                if (empty($roleColumnVisibility) || !isset($roleColumnVisibility[$targetRoleId])) {
                                  $allowedCount = count($roles);
                                } else {
                                  $allowedCount = count($roleColumnVisibility[$targetRoleId]);
                                }
                
                                if ($superAdminRoleId > 0 && (!isset($roleColumnVisibility[$targetRoleId][$superAdminRoleId]))) {
                                  $allowedCount++;
                                }
                              ?>
                
                              <button type="button" class="cv-toggle" data-target-role="<?= $targetRoleId ?>">
                                Visible to: <?= (int)$allowedCount ?> roles <i class="fas fa-chevron-down"></i>
                              </button>
                
                              <div class="cv-menu" data-target-role="<?= $targetRoleId ?>">
                                <?php foreach ($roles as $viewerRole): ?>
                                  <?php
                                    $viewerId = (int)$viewerRole['id'];
                                    $isLocked = ($superAdminRoleId > 0 && $viewerId === $superAdminRoleId);
                
                                    $isChecked =
                                      $isLocked ||
                                      empty($roleColumnVisibility) ||
                                      !isset($roleColumnVisibility[$targetRoleId]) ||
                                      isset($roleColumnVisibility[$targetRoleId][$viewerId]);
                                  ?>
                                  <label class="cv-item">
                                    <input
                                      type="checkbox"
                                      class="cv-check"
                                      data-target-role-id="<?= $targetRoleId ?>"
                                      data-viewer-role-id="<?= $viewerId ?>"
                                      <?= $isChecked ? 'checked' : '' ?>
                                      <?= $isLocked ? 'disabled' : '' ?>
                                    >
                                    <span><?= ucwords(str_replace('_', ' ', $viewerRole['name'])) ?></span>
                                    <?php if ($isLocked): ?><em class="cv-note">always</em><?php endif; ?>
                                  </label>
                                <?php endforeach; ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                      </th>
                    <?php endforeach; ?>
                
                  </tr>
                </thead>
               <tbody>
                  <?php foreach ($permissionGroups as $group => $permNames): ?>
                  <?php
                      // ✅ Hide Special Settings group from everyone except Super Admin / allowed users
                      if ($group === 'Special Settings' && !$isSuperAdmin && !$canManageRoleViewable) {
                        continue;
                      }
                    ?>
                    <tr class="group-header" data-group="<?= $group ?>" style="background: rgba(67,97,238,0.07); font-weight: bold; cursor: pointer;">
                      <td colspan="<?= count($visibleRoles) + 1 ?>" style="text-align: left; color: var(--primary);">
                        <i class="fas fa-chevron-down toggle-icon" style="margin-right: 10px;"></i> <?= $group ?>
                      </td>
                    </tr>
                    <?php foreach ($permNames as $permName): ?>
                      <?php
                        $perm = array_filter($permissions, function($p) use ($permName) {
                            return $p['name'] === $permName;
                        });
                        if (empty($perm)) continue;
                        $perm = array_values($perm)[0]; // extract the first matching
                      ?>
                      <tr class="permission-row" data-group="<?= $group ?>">
                        <td>
                          <?= get_permission_icon($perm['name']) ?>
                          <?= get_permission_label($perm['name']) ?>
                        </td>
                        <?php foreach ($visibleRoles as $role): ?>
                          <td>
                            <?php
                              $roleId = (int)$role['id'];
                              $isManageRoleViewableRow = ($perm['name'] === 'manage_role_viewable');
                              $isSuperAdminCol = ($superAdminRoleId > 0 && $roleId === (int)$superAdminRoleId);
                            ?>
                        
                            <?php if ($isManageRoleViewableRow): ?>
                              <?php
                                // ✅ Show checkboxes in every role column for this permission
                                // ✅ Only super_admin can change them (others see disabled/read-only)
                                // ✅ super_admin role itself is always locked ON
                                $isLockedRole = ($superAdminRoleId > 0 && $roleId === (int)$superAdminRoleId);
                                $isChecked    = isset($rolePermissions[$roleId][(int)$perm['id']]);
                            
                                $checkedAttr  = ($isLockedRole || $isChecked) ? 'checked' : '';
                                $disabledAttr = (!$isSuperAdmin || $isLockedRole) ? 'disabled' : '';
                            
                                $titleAttr = $isLockedRole
                                  ? 'super_admin always has this permission'
                                  : ($isSuperAdmin ? 'Toggle this permission for this role' : 'Only super_admin can change this permission');
                              ?>
                            
                              <input type="checkbox"
                                name="permissions[<?= $roleId ?>][<?= (int)$perm['id'] ?>]"
                                data-role-id="<?= $roleId ?>"
                                data-permission-id="<?= (int)$perm['id'] ?>"
                                data-permission-name="<?= htmlspecialchars($perm['name']) ?>"
                                value="<?= (int)$perm['id'] ?>"
                                <?= $checkedAttr ?>
                                <?= $disabledAttr ?>
                                title="<?= htmlspecialchars($titleAttr) ?>"
                              >
                            
                            <?php if ($isSuperAdmin && $isSuperAdminCol): ?>
                              <?php
                                $permId = (int)$perm['id']; // manage_role_viewable permission id
                            
                                // Count how many roles currently have this permission (super_admin always counts)
                                $allowedCount = 0;
                                foreach ($roles as $rr) {
                                  $rid = (int)$rr['id'];
                                  if ($superAdminRoleId > 0 && $rid === (int)$superAdminRoleId) { $allowedCount++; continue; }
                                  if (isset($rolePermissions[$rid][$permId])) $allowedCount++;
                                }
                              ?>
                            
                              <div class="pv-block" style="margin-top:8px;">
                                <button type="button" class="pv-toggle" data-perm-id="<?= $permId ?>">
                                  Visible to: <?= (int)$allowedCount ?> roles <i class="fas fa-chevron-down"></i>
                                </button>
                            
                                <div class="pv-menu" data-perm-id="<?= $permId ?>">
                                  <?php foreach ($roles as $viewerRole): ?>
                                    <?php
                                      $viewerId = (int)$viewerRole['id'];
                                      $isLocked = ($superAdminRoleId > 0 && $viewerId === (int)$superAdminRoleId);
                            
                                      $isChecked2 = $isLocked || isset($rolePermissions[$viewerId][$permId]);
                                    ?>
                                    <label class="pv-item">
                                      <input
                                        type="checkbox"
                                        class="pv-check"
                                        data-role-id="<?= $viewerId ?>"
                                        data-permission-id="<?= $permId ?>"
                                        data-permission-name="manage_role_viewable"
                                        <?= $isChecked2 ? 'checked' : '' ?>
                                        <?= $isLocked ? 'disabled' : '' ?>
                                      >
                                      <span><?= ucwords(str_replace('_', ' ', $viewerRole['name'])) ?></span>
                                      <?php if ($isLocked): ?><em class="pv-note">always</em><?php endif; ?>
                                    </label>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>

                            <?php else: ?>
                            
                              <input type="checkbox"
                                name="permissions[<?= $roleId ?>][<?= (int)$perm['id'] ?>]"
                                data-role-id="<?= $roleId ?>"
                                data-permission-id="<?= (int)$perm['id'] ?>"
                                data-permission-name="<?= htmlspecialchars($perm['name']) ?>"
                                value="<?= (int)$perm['id'] ?>"
                                <?= isset($rolePermissions[$roleId][$perm['id']]) ? 'checked' : '' ?>
                              >
                            
                            <?php endif; ?>

                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                 <tr class="padding-row" style="visibility: hidden;">
                   <td colspan="<?= count($visibleRoles) + 1 ?>">&nbsp;</td>
                 </tr>
                </tbody>
            </table>
          </div>
        </form>
      </div> <!-- page-body -->
    </div> <!-- page-wrapper -->
  </div> <!-- main-content -->
</div> <!-- app-container -->

<script>
(function () {
  const form = document.getElementById('permissionsForm');
  if (!form) return;

  let bulkMode = false;
  let reloadTimer = null;

  function scheduleReload() {
    if (reloadTimer) clearTimeout(reloadTimer);
    reloadTimer = setTimeout(() => location.reload(), 350); // ✅ allows multiple quick changes
  }

  // ✅ Always call AJAX files from the SAME folder as this page
    const AJAX_BASE = window.location.pathname.replace(/\/[^\/]*$/, ''); 
    const PERM_AJAX_URL = `${AJAX_BASE}/update_permission_ajax.php`;
    const ROLE_COL_URL  = `${AJAX_BASE}/update_role_column_visibility.php`;
    const RECOMM_URL    = `${AJAX_BASE}/get_recommended_permissions.php`;
    
    async function postPermission(roleId, permissionId, assign) {
      const res = await fetch(PERM_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `role_id=${encodeURIComponent(roleId)}&permission_id=${encodeURIComponent(permissionId)}&assign=${encodeURIComponent(assign)}`
      });
    
      // ✅ If file path is wrong, you'll get 404/HTML — handle it clearly
      if (!res.ok) {
        throw new Error(`HTTP ${res.status} (${res.statusText}) — update_permission_ajax.php not reachable at ${PERM_AJAX_URL}`);
      }
    
      let data;
      try {
        data = await res.json();
      } catch (e) {
        throw new Error(`Server did not return JSON. Check PHP errors. URL: ${PERM_AJAX_URL}`);
      }
    
      if (!data.success) throw new Error(data.message || 'Failed saving permission');
      return true;
    }

  async function bulkApply(changes) {
    if (!changes.length) return;

    bulkMode = true;
    try {
      // ✅ sequential = safest + easiest to debug
      for (const c of changes) {
        await postPermission(c.roleId, c.permissionId, c.assign);
      }
      location.reload(); // ✅ ONE reload at the end
    } catch (e) {
      alert('Error: ' + e.message);
      location.reload();
    } finally {
      bulkMode = false;
    }
  }

  // ✅ Single checkbox changes (includes pv-check, excludes cv-check)
  form.querySelectorAll('input[type="checkbox"]:not(.cv-check)').forEach(box => {
    box.addEventListener('change', async function () {
      if (bulkMode) return;

      const roleId = this.dataset.roleId;
      const permissionId = this.dataset.permissionId;
      if (!roleId || !permissionId) return;

      const assign = this.checked ? 1 : 0;

      try {
        await postPermission(roleId, permissionId, assign);
        scheduleReload(); // ✅ debounce reload so multiple toggles can be done
      } catch (e) {
        alert('Error: ' + e.message);
        this.checked = !this.checked; // revert
      }
    });
  });

  // ✅ Select All (NO per-checkbox reload)
  document.querySelectorAll('.select-all').forEach(btn => {
    btn.addEventListener('click', () => {
      const roleId = btn.dataset.role;

      const boxes = Array.from(document.querySelectorAll(
        `#permissionsForm input[type="checkbox"][data-role-id="${roleId}"]:not(.pv-check):not(.cv-check):not(:disabled)`
      ));

      const changes = [];
      boxes.forEach(b => {
        if (!b.checked) {
          b.checked = true;
          changes.push({ roleId: b.dataset.roleId, permissionId: b.dataset.permissionId, assign: 1 });
        }
      });

      bulkApply(changes);
    });
  });

  // ✅ Recommended (NO per-checkbox reload)
  document.querySelectorAll('.select-recommended').forEach(btn => {
    btn.addEventListener('click', async () => {
      const roleId = btn.dataset.role;
      const roleName = btn.dataset.roleName;

      try {
        const rec = await fetch(RECOMM_URL + '?role=' + encodeURIComponent(roleName)).then(r => r.json());

        const boxes = Array.from(document.querySelectorAll(
          `#permissionsForm input[type="checkbox"][data-role-id="${roleId}"]:not(.pv-check):not(.cv-check):not(:disabled)`
        ));

        const changes = [];
        boxes.forEach(b => {
          const permName = b.dataset.permissionName;
          const should = rec.includes(permName);
          if (b.checked !== should) {
            b.checked = should;
            changes.push({ roleId: b.dataset.roleId, permissionId: b.dataset.permissionId, assign: should ? 1 : 0 });
          }
        });

        bulkApply(changes);
      } catch (e) {
        alert('Error: ' + e.message);
      }
    });
  });

})();
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggles = document.querySelectorAll('.pv-toggle');
  const menus   = document.querySelectorAll('.pv-menu');

  function closeAll() { menus.forEach(m => m.style.display = 'none'); }

  menus.forEach(m => m.addEventListener('click', e => e.stopPropagation()));

  toggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();

      const permId = btn.dataset.permId;
      const menu = document.querySelector(`.pv-menu[data-perm-id='${permId}']`);
      if (!menu) return;

      const isOpen = (menu.style.display === 'block');
      closeAll();
      if (isOpen) return;

      const rect = btn.getBoundingClientRect();
      menu.style.display = 'block';
      menu.style.top  = (rect.bottom + 8) + 'px';
      menu.style.left = (rect.left + rect.width / 2) + 'px';
      menu.style.transform = 'translateX(-50%)';

      const mRect = menu.getBoundingClientRect();
      const pad = 10;
      if (mRect.right > window.innerWidth - pad) {
        menu.style.left = (window.innerWidth - (mRect.width / 2) - pad) + 'px';
      }
      if (mRect.left < pad) {
        menu.style.left = ((mRect.width / 2) + pad) + 'px';
      }
    });
  });

  document.addEventListener('click', closeAll);
  window.addEventListener('resize', closeAll);
  window.addEventListener('scroll', closeAll, true);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const groupHeaders = document.querySelectorAll('.group-header');
  const allPermissionRows = document.querySelectorAll('.permission-row');

  function toggleGroup(groupName, collapse) {
    allPermissionRows.forEach(row => {
      if (row.dataset.group === groupName) {
        row.style.display = collapse ? 'none' : '';
      }
    });
  }

  // Restore saved collapse state
  const saved = JSON.parse(localStorage.getItem('collapsedPermissionGroups') || '{}');
  groupHeaders.forEach(header => {
    const groupName = header.dataset.group;
    const isCollapsed = saved[groupName];
    if (isCollapsed) {
      header.classList.add('collapsed');
      toggleGroup(groupName, true);
    }
  });

  // Toggle group on header click
  groupHeaders.forEach(header => {
    header.addEventListener('click', () => {
      const groupName = header.dataset.group;
      const isCollapsed = header.classList.toggle('collapsed');
      toggleGroup(groupName, isCollapsed);

      const saved = JSON.parse(localStorage.getItem('collapsedPermissionGroups') || '{}');
      saved[groupName] = isCollapsed;
      localStorage.setItem('collapsedPermissionGroups', JSON.stringify(saved));
    });
  });

  // Expand All
  document.getElementById('expandAll').addEventListener('click', () => {
    groupHeaders.forEach(header => header.classList.remove('collapsed'));
    const state = {};
    groupHeaders.forEach(header => {
      const groupName = header.dataset.group;
      toggleGroup(groupName, false);
      state[groupName] = false;
    });
    localStorage.setItem('collapsedPermissionGroups', JSON.stringify(state));
  });

  // Collapse All
  document.getElementById('collapseAll').addEventListener('click', () => {
    groupHeaders.forEach(header => header.classList.add('collapsed'));
    const state = {};
    groupHeaders.forEach(header => {
      const groupName = header.dataset.group;
      toggleGroup(groupName, true);
      state[groupName] = true;
    });
    localStorage.setItem('collapsedPermissionGroups', JSON.stringify(state));
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggles = document.querySelectorAll('.cv-toggle');
  const menus   = document.querySelectorAll('.cv-menu');

  // ✅ Same-folder AJAX URLs (prevents undefined var + path issues)
  const AJAX_BASE = window.location.pathname.replace(/\/[^\/]*$/, '');
  const ROLE_COL_URL = `${AJAX_BASE}/update_role_column_visibility.php`;

  function closeAll() {
    menus.forEach(m => m.style.display = 'none');
  }

  // Prevent click inside menu from closing it
  menus.forEach(m => m.addEventListener('click', e => e.stopPropagation()));

  toggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();

      const targetRole = btn.dataset.targetRole;
      const menu = document.querySelector(`.cv-menu[data-target-role='${targetRole}']`);
      if (!menu) return;

      const isOpen = (menu.style.display === 'block');
      closeAll();
      if (isOpen) return;

      // Position menu under the button (fixed so it won't be clipped)
      const rect = btn.getBoundingClientRect();
      menu.style.display = 'block';
      menu.style.top  = (rect.bottom + 8) + 'px';
      menu.style.left = (rect.left + rect.width / 2) + 'px';
      menu.style.transform = 'translateX(-50%)';

      // Keep within viewport
      const mRect = menu.getBoundingClientRect();
      const pad = 10;
      if (mRect.right > window.innerWidth - pad) {
        menu.style.left = (window.innerWidth - (mRect.width / 2) - pad) + 'px';
      }
      if (mRect.left < pad) {
        menu.style.left = ((mRect.width / 2) + pad) + 'px';
      }
    });
  });

  document.addEventListener('click', closeAll);
  window.addEventListener('resize', closeAll);
  window.addEventListener('scroll', closeAll, true);

  // Save visibility changes
  document.querySelectorAll('.cv-check').forEach(chk => {
    chk.addEventListener('change', () => {
      const targetRoleId = chk.dataset.targetRoleId;
      const viewerRoleId = chk.dataset.viewerRoleId;
      const allow = chk.checked ? 1 : 0;

      fetch(ROLE_COL_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `target_role_id=${encodeURIComponent(targetRoleId)}&viewer_role_id=${encodeURIComponent(viewerRoleId)}&allow=${allow}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + (data.message || 'Failed'));
          chk.checked = !chk.checked; // revert
        }
      })
      .catch(() => {
        alert('Network error while saving.');
        chk.checked = !chk.checked; // revert
      });
    });
  });
});
</script>

  <?php require 'scripts.php'; ?>
</body>
</html>
