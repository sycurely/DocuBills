<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $manager = Role::where('name', 'manager')->first();
        $assistant = Role::where('name', 'assistant')->first();
        $viewer = Role::where('name', 'viewer')->first();

        // Super Admin gets all permissions
        if ($superAdmin) {
            $allPermissions = Permission::pluck('id');
            $superAdmin->permissions()->sync($allPermissions);
        }

        // Admin gets ALL permissions (full/unrestricted access, same as super_admin)
        if ($admin) {
            $allPermissions = Permission::pluck('id');
            $admin->permissions()->sync($allPermissions);
        }

        // Manager permissions
        if ($manager) {
            $managerPermissions = Permission::whereIn('name', [
                'view_dashboard',
                'create_invoice',
                'view_invoices',
                'view_invoice_history',
                'view_invoice_payment_info',
                'view_clients',
                'add_client',
                'edit_client',
                'view_expenses',
                'view_expenses_trashbin',
                'access_reports',
                'access_history',
            ])->pluck('id');
            $manager->permissions()->sync($managerPermissions);
        }

        // Assistant permissions
        if ($assistant) {
            $assistantPermissions = Permission::whereIn('name', [
                'view_dashboard',
                'view_invoices',
                'view_invoice_history',
                'view_clients',
                'add_client',
                'edit_client',
                'view_expenses',
            ])->pluck('id');
            $assistant->permissions()->sync($assistantPermissions);
        }

        // Viewer permissions
        if ($viewer) {
            $viewerPermissions = Permission::whereIn('name', [
                'view_dashboard',
            ])->pluck('id');
            $viewer->permissions()->sync($viewerPermissions);
        }
    }
}
