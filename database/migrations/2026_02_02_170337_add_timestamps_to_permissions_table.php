<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds created_at/updated_at for legacy permissions tables (e.g. docubill_old).
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('description');
            }
            if (! Schema::hasColumn('permissions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('permissions', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
