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
        if (!Schema::hasTable('invoice_custom_reminders')) {
            Schema::create('invoice_custom_reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->date('reminder_date');
                $table->unsignedInteger('offset_days')->nullable();
                $table->string('offset_base', 20)->nullable();
                $table->unsignedBigInteger('template_id')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->index(['reminder_date', 'status'], 'idx_invoice_custom_reminder_due');
                $table->unique(['invoice_id', 'reminder_date', 'template_id'], 'uniq_invoice_custom_reminder');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_custom_reminders');
    }
};
