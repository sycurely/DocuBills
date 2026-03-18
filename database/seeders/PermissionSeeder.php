<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard & Reports
            ['name' => 'view_dashboard', 'description' => 'View dashboard'],
            ['name' => 'access_reports', 'description' => 'Access reports'],
            ['name' => 'access_history', 'description' => 'Access history'],

            // Invoice Permissions
            ['name' => 'create_invoice', 'description' => 'Create invoice'],
            ['name' => 'delete_invoice', 'description' => 'Delete invoice'],
            ['name' => 'edit_invoice', 'description' => 'Edit invoice'],
            ['name' => 'save_invoice', 'description' => 'Save invoice'],
            ['name' => 'view_invoices', 'description' => 'View invoices'],
            ['name' => 'mark_invoice_paid', 'description' => 'Mark invoice as paid'],
            ['name' => 'download_invoice_pdf', 'description' => 'Download invoice PDF'],
            ['name' => 'email_invoice', 'description' => 'Email invoice'],
            ['name' => 'restore_invoices', 'description' => 'Restore deleted invoices'],
            ['name' => 'view_invoice_payment_info', 'description' => 'View invoice payment info'],
            ['name' => 'delete_forever', 'description' => 'Delete invoice forever'],
            ['name' => 'view_invoice_history', 'description' => 'View invoice history'],
            ['name' => 'view_invoice_logs', 'description' => 'View invoice logs'],
            ['name' => 'add_invoice_field', 'description' => 'Add invoice field'],
            ['name' => 'show_due_date', 'description' => 'Show due date'],
            ['name' => 'show_due_time', 'description' => 'Show due time'],
            ['name' => 'show_invoice_date', 'description' => 'Show invoice date'],
            ['name' => 'show_invoice_time', 'description' => 'Show invoice time'],
            ['name' => 'show_invoice_checkboxes', 'description' => 'Show invoice checkboxes'],
            ['name' => 'toggle_bank_details', 'description' => 'Toggle bank details'],
            ['name' => 'manage_recurring_invoices', 'description' => 'Manage recurring invoices'],

            // Client Permissions
            ['name' => 'access_clients_tab', 'description' => 'Access clients tab'],
            ['name' => 'view_clients', 'description' => 'View own clients'],
            ['name' => 'view_all_clients', 'description' => 'View all clients'],
            ['name' => 'add_client', 'description' => 'Add client'],
            ['name' => 'edit_client', 'description' => 'Edit client'],
            ['name' => 'delete_client', 'description' => 'Delete client'],
            ['name' => 'restore_clients', 'description' => 'Restore deleted clients'],
            ['name' => 'undo_recent_client', 'description' => 'Undo recent client deletion'],
            ['name' => 'undo_all_clients', 'description' => 'Undo all client deletions'],
            ['name' => 'export_clients', 'description' => 'Export clients'],
            ['name' => 'search_clients', 'description' => 'Search clients'],

            // Expense Permissions
            ['name' => 'access_expenses_tab', 'description' => 'Access expenses tab'],
            ['name' => 'view_expenses', 'description' => 'View own expenses'],
            ['name' => 'view_all_expenses', 'description' => 'View all expenses'],
            ['name' => 'add_expense', 'description' => 'Add expense'],
            ['name' => 'edit_expense', 'description' => 'Edit expense'],
            ['name' => 'delete_expense', 'description' => 'Delete expense'],
            ['name' => 'undo_recent_expense', 'description' => 'Undo recent expense deletion'],
            ['name' => 'undo_all_expenses', 'description' => 'Undo all expense deletions'],
            ['name' => 'change_expense_status', 'description' => 'Change expense status'],
            ['name' => 'view_expense_details', 'description' => 'View expense details'],
            ['name' => 'search_expenses', 'description' => 'Search expenses'],
            ['name' => 'export_expenses', 'description' => 'Export expenses'],

            // Expenses Trash Bin
            ['name' => 'view_expenses_trashbin', 'description' => 'View own expenses trash bin'],
            ['name' => 'view_all_expenses_trashbin', 'description' => 'View all expenses trash bin'],
            ['name' => 'delete_expense_forever', 'description' => 'Delete expense forever'],

            // Settings Permissions
            ['name' => 'update_basic_settings', 'description' => 'Update basic settings'],
            ['name' => 'access_basic_settings', 'description' => 'Access basic settings'],
            ['name' => 'assign_roles', 'description' => 'Assign roles'],
            ['name' => 'manage_users', 'description' => 'Manage users'],
            ['name' => 'manage_users_page', 'description' => 'Manage users page'],
            ['name' => 'add_user', 'description' => 'Add new user'],
            ['name' => 'edit_user', 'description' => 'Edit user'],
            ['name' => 'delete_user', 'description' => 'Delete user'],
            ['name' => 'suspend_users', 'description' => 'Suspend users'],
            ['name' => 'manage_permissions', 'description' => 'Manage permissions'],
            ['name' => 'manage_payment_methods', 'description' => 'Manage payment methods'],
            ['name' => 'manage_card_payments', 'description' => 'Manage card payments'],
            ['name' => 'manage_bank_details', 'description' => 'Manage bank details'],
            ['name' => 'manage_reminder_settings', 'description' => 'Manage reminder settings'],
            ['name' => 'access_email_templates_page', 'description' => 'Access email templates page'],
            ['name' => 'add_email_template', 'description' => 'Add email template'],
            ['name' => 'edit_email_template', 'description' => 'Edit email template'],
            ['name' => 'delete_email_template', 'description' => 'Delete email template'],
            ['name' => 'manage_notification_categories', 'description' => 'Manage notification categories'],
            ['name' => 'manage_role_viewable', 'description' => 'Manage role viewable'],

            // Trash Bin
            ['name' => 'access_trashbin', 'description' => 'Access trash bin'],
            ['name' => 'view_all_trash', 'description' => 'View all trash'],
            ['name' => 'restore_deleted_items', 'description' => 'Restore deleted items'],

            // Email & Support
            ['name' => 'access_support', 'description' => 'Access support'],

            // Login Logs
            ['name' => 'view_login_logs', 'description' => 'View login logs'],
            ['name' => 'terminate_sessions', 'description' => 'Terminate sessions'],
            ['name' => 'terminate_own_session', 'description' => 'Terminate own session'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
