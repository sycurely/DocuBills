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
        if (!Schema::hasTable('invoice_email_configurations')) {
            Schema::create('invoice_email_configurations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->unique();
                $table->unsignedBigInteger('delivery_template_id')->nullable();
                $table->unsignedBigInteger('payment_confirmation_template_id')->nullable();
                $table->timestamps();

                $table->index('delivery_template_id', 'idx_invoice_email_cfg_delivery_tpl');
                $table->index('payment_confirmation_template_id', 'idx_invoice_email_cfg_payment_tpl');
            });
        }

        if (!Schema::hasTable('invoice_reminder_template_bindings')) {
            Schema::create('invoice_reminder_template_bindings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('rule_id', 64);
                $table->unsignedBigInteger('template_id');
                $table->timestamps();

                $table->index(['invoice_id', 'rule_id'], 'idx_inv_rule');
                $table->index(['invoice_id', 'template_id'], 'idx_inv_tpl');
                $table->unique(['invoice_id', 'rule_id', 'template_id'], 'uniq_inv_rule_tpl');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_template_bindings');
        Schema::dropIfExists('invoice_email_configurations');
    }
};
