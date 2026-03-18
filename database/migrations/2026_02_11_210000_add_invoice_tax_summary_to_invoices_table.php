<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        if (!Schema::hasColumn('invoices', 'invoice_tax_summary')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->text('invoice_tax_summary')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        if (Schema::hasColumn('invoices', 'invoice_tax_summary')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('invoice_tax_summary');
            });
        }
    }
};

