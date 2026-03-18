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
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->string('vendor');
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('payment_proof')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('status')->default('Unpaid'); // 'Paid' or 'Unpaid'
            $table->string('payment_method')->nullable(); // 'Cash', 'Check', 'Bank Transfer', 'Credit Card', etc.
            $table->string('email_cc')->nullable();
            $table->string('email_bcc')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('expenses');
    }
};
