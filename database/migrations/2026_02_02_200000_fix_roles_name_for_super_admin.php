<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix role id 1 having null/empty name so super_admin is recognized (noraparker has role_id 1).
     */
    public function up(): void
    {
        DB::table('roles')->where('id', 1)->update(['name' => 'super_admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely reverse without knowing previous value
    }
};
