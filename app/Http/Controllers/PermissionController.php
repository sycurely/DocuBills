<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Show the permission matrix.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('settings.permissions', compact('roles', 'permissions'));
    }

    /**
     * Update role permissions.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('settings.permissions')->with('success', 'Permissions updated successfully.');
    }

    /**
     * Get recommended permissions for a role.
     */
    public function getRecommended(Request $request)
    {
        $roleName = $request->get('role');
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json([]);
        }

        // Get recommended permissions based on role
        $recommended = $this->getRecommendedPermissions($roleName);

        return response()->json($recommended);
    }

    /**
     * Get recommended permissions for a role.
     */
    private function getRecommendedPermissions(string $roleName): array
    {
        $allPermissions = Permission::pluck('name')->toArray();

        $recommended = [
            'super_admin' => $allPermissions,
            'admin' => [
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
                'access_reports',
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
                'access_history',
            ],
            'assistant' => [
                'view_dashboard',
                'view_invoices',
                'view_invoice_history',
                'view_clients',
                'view_expenses',
            ],
            'viewer' => [
                'view_dashboard',
            ],
        ];

        return $recommended[$roleName] ?? [];
    }
}
