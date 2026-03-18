<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnsureNoraParkerAdminSeeder extends Seeder
{
    /**
     * Ensure user noraparker exists with super_admin role (full system access).
     * Creates the user if missing; otherwise assigns/confirms super_admin role.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if (!$superAdminRole) {
            $this->command?->warn('Super Admin role not found. Run RoleSeeder first.');
            return;
        }

        $user = User::where('username', 'noraparker')->first();

        if (!$user) {
            User::create([
                'username' => 'noraparker',
                'name' => 'Nora Parker',
                'email' => 'noraparker@example.com',
                'full_name' => 'Nora Parker',
                'password' => 'password',
                'role_id' => $superAdminRole->id,
                'is_suspended' => false,
            ]);
            $this->command?->info('User noraparker has been created with super_admin role (full access).');
            return;
        }

        $user->role_id = $superAdminRole->id;
        $user->save();

        $this->command?->info('User noraparker has been assigned the super_admin role (full access).');
    }
}
