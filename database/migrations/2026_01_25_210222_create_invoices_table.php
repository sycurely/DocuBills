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
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('bill_to_name')->nullable();
            $table->json('bill_to_json')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->dateTime('invoice_date');
            $table->dateTime('due_date')->nullable();
            $table->string('status')->default('Unpaid'); // 'Paid' or 'Unpaid'
            $table->longText('html')->nullable(); // Invoice HTML content
            $table->text('payment_link')->nullable();
            $table->string('payment_provider')->default('Manual'); // 'Stripe', 'Test', 'Manual'
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('show_bank_details')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable(); // 'monthly', 'weekly', etc.
            $table->date('next_run_date')->nullable();
            $table->string('currency_code', 10)->default('USD');
            $table->string('currency_display', 10)->nullable();
            $table->string('invoice_title_bg', 7)->default('#FFDC00'); // Hex color
            $table->string('invoice_title_text', 7)->default('#0033D9'); // Hex color
            $table->text('invoice_tax_summary')->nullable(); // Tax calculation summary
            $table->timestamps();
            $table->softDeletes();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
