<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taxes') || Schema::hasColumn('taxes', 'created_by')) {
            return;
        }

        Schema::table('taxes', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        $ownerId = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->whereIn('roles.name', ['super_admin', 'admin'])
            ->orderBy('users.id')
            ->value('users.id');

        if ($ownerId) {
            DB::table('taxes')->whereNull('created_by')->update(['created_by' => $ownerId]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('taxes') || !Schema::hasColumn('taxes', 'created_by')) {
            return;
        }

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
