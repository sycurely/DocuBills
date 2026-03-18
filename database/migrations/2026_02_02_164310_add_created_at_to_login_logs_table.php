<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds created_at for legacy login_logs tables that don't have it (e.g. docubill_old).
     */
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('login_logs', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            if (Schema::hasColumn('login_logs', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
