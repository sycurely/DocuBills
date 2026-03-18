<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a default admin user for local development when no users exist.
     */
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        $adminRole = Role::where('name', 'admin')->first()
            ?? Role::where('name', 'super_admin')->first();

        User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'full_name' => 'Admin User',
            'password' => 'password',
            'role_id' => $adminRole?->id,
            'is_suspended' => false,
        ]);
    }
}
