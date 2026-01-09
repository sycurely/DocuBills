<?php
require_once 'config.php';

$roleName = $_GET['role'] ?? '';
$roleName = strtolower(trim($roleName));

// Fetch all permission names from DB
$allPermissions = $pdo->query("SELECT name FROM permissions")->fetchAll(PDO::FETCH_COLUMN);

// Recommended permission map per role
$recommended = [
  'super_admin' => $allPermissions, // ✅ All permissions from DB

  'admin' => [
    'view_dashboard',
    'create_invoice',
    'view_invoices',
    'edit_invoice',
    'mark_invoice_paid',
    'download_invoice_pdf',
    'email_invoice',
    'view_invoice_history',
    'view_invoice_payment_info',

    'add_client',
    'edit_client',
    'delete_client',
    'view_clients',

    'add_expense',
    'edit_expense',
    'delete_expense',
    'view_expenses',
    'view_expenses_trashbin',
    'restore_expenses',

    'assign_roles',
    'edit_user',
    'manage_users_page',

    'add_email_template',
    'edit_email_template',
    'delete_email_template',
    'access_email_templates_page',

    'access_basic_settings_page',
    'update_basic_settings',
    'manage_payment_methods',

    'access_history',
    'access_reports'
  ],

  'manager' => [
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
  ],

  'assistant' => [
    'view_dashboard',
    'view_invoices',
    'view_invoice_history',
    'view_clients',
    'view_expenses'
  ],

  'viewer' => ['view_dashboard']
];

header('Content-Type: application/json');
echo json_encode($recommended[$roleName] ?? []);
