<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'workspace_owner_id')) {
                $table->foreignId('workspace_owner_id')->nullable()->after('role_id')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('users')->whereNull('workspace_owner_id')->update([
            'workspace_owner_id' => DB::raw('id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'workspace_owner_id')) {
                $table->dropConstrainedForeignId('workspace_owner_id');
            }
        });
    }
};
