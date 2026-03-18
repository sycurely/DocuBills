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
        if (!Schema::hasTable('invoice_reminder_logs')) {
            return;
        }

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_reminder_logs', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('rule_id');
                $table->index('template_id', 'idx_invoice_reminder_logs_template_id');
            }
        });

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_invoice_reminder_cycle');
            } catch (\Throwable $e) {
                // Unique index may not exist in older databases.
            }
        });

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->unique(
                ['invoice_id', 'reminder_type', 'rule_id', 'template_id', 'status_sent_scope'],
                'uniq_invoice_reminder_template_cycle'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invoice_reminder_logs')) {
            return;
        }

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_invoice_reminder_template_cycle');
            } catch (\Throwable $e) {
                // Ignore missing index.
            }

            if (Schema::hasColumn('invoice_reminder_logs', 'template_id')) {
                try {
                    $table->dropIndex('idx_invoice_reminder_logs_template_id');
                } catch (\Throwable $e) {
                    // Ignore missing index.
                }
                $table->dropColumn('template_id');
            }
        });

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->unique(
                ['invoice_id', 'reminder_type', 'rule_id', 'status_sent_scope'],
                'uniq_invoice_reminder_cycle'
            );
        });
    }
};
