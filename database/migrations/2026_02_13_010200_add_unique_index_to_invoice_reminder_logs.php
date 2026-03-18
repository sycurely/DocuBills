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
        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->unique(
                ['invoice_id', 'reminder_type', 'rule_id', 'status_sent_scope'],
                'uniq_invoice_reminder_cycle'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->dropUnique('uniq_invoice_reminder_cycle');
        });
    }
};
