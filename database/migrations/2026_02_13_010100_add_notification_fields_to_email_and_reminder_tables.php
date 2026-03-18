<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('email_templates', 'assigned_notification_type')) {
                    if (Schema::hasColumn('email_templates', 'category')) {
                        $table->string('assigned_notification_type', 120)->nullable()->after('category');
                    } else {
                        $table->string('assigned_notification_type', 120)->nullable();
                    }
                }
            });
        }

        if (!Schema::hasTable('invoice_reminder_logs')) {
            return;
        }

        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_reminder_logs', 'reminder_type')) {
                if (Schema::hasColumn('invoice_reminder_logs', 'status')) {
                    $table->string('reminder_type', 50)->nullable()->after('status');
                } else {
                    $table->string('reminder_type', 50)->nullable();
                }
            }

            if (!Schema::hasColumn('invoice_reminder_logs', 'recipient_email')) {
                if (Schema::hasColumn('invoice_reminder_logs', 'sent_at')) {
                    $table->string('recipient_email', 255)->nullable()->after('sent_at');
                } else {
                    $table->string('recipient_email', 255)->nullable();
                }
            }

            if (!Schema::hasColumn('invoice_reminder_logs', 'rule_id')) {
                $table->string('rule_id', 64)->nullable();
            }

            if (!Schema::hasColumn('invoice_reminder_logs', 'status_sent_scope')) {
                $table->date('status_sent_scope')->nullable();
            }
        });

        if (Schema::hasColumn('invoice_reminder_logs', 'reminder_key')) {
            DB::table('invoice_reminder_logs')
                ->whereNull('reminder_type')
                ->update(['reminder_type' => DB::raw('reminder_key')]);
        }

        if (Schema::hasColumn('invoice_reminder_logs', 'sent_to')) {
            DB::table('invoice_reminder_logs')
                ->whereNull('recipient_email')
                ->update(['recipient_email' => DB::raw('sent_to')]);
        }

        DB::table('invoice_reminder_logs')
            ->whereNull('rule_id')
            ->whereNotNull('reminder_type')
            ->update(['rule_id' => DB::raw('reminder_type')]);

        DB::table('invoice_reminder_logs')
            ->whereNull('status_sent_scope')
            ->whereNotNull('sent_at')
            ->update(['status_sent_scope' => DB::raw('DATE(sent_at)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_reminder_logs', 'status_sent_scope')) {
                $table->dropColumn('status_sent_scope');
            }
            if (Schema::hasColumn('invoice_reminder_logs', 'rule_id')) {
                $table->dropColumn('rule_id');
            }
        });

        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'assigned_notification_type')) {
                $table->dropColumn('assigned_notification_type');
            }
        });
    }
};
