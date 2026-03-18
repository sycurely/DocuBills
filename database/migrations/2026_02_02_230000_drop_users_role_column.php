<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop legacy users.role enum column that shadows the role() relationship,
     * and backfill role_id from that column for users missing role_id.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            return;
        }

        $roleIdsByName = DB::table('roles')->pluck('id', 'name');
        if ($roleIdsByName->isNotEmpty()) {
            foreach ($roleIdsByName as $name => $id) {
                DB::table('users')
                    ->whereNull('role_id')
                    ->where('role', $name)
                    ->update(['role_id' => $id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     * Recreate users.role enum and backfill from role_id where possible.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'manager', 'assistant', 'viewer'])
                ->nullable()
                ->after('role_id');
        });

        $roles = DB::table('roles')->get(['id', 'name'])->keyBy('id');
        foreach ($roles as $id => $role) {
            DB::table('users')
                ->where('role_id', $id)
                ->update(['role' => $role->name]);
        }
    }
};
