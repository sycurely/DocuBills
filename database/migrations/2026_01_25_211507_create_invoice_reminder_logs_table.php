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
        if (! Schema::hasTable('invoice_reminder_logs')) {
            Schema::create('invoice_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->timestamp('sent_at');
            $table->string('recipient_email');
            $table->string('status')->default('sent'); // 'sent', 'failed'
            $table->string('reminder_type')->nullable(); // 'before_due', 'on_due', 'after_3', etc.
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_logs');
    }
};
