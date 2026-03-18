<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'bill_to_name',
        'bill_to_json',
        'total_amount',
        'invoice_date',
        'due_date',
        'status',
        'html',
        'payment_link',
        'payment_provider',
        'created_by',
        'show_bank_details',
        'is_recurring',
        'recurrence_type',
        'next_run_date',
        'currency_code',
        'currency_display',
        'invoice_title_bg',
        'invoice_title_text',
        'invoice_tax_summary',
    ];

    protected $casts = [
        'bill_to_json' => 'array',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'show_bank_details' => 'boolean',
        'is_recurring' => 'boolean',
        'next_run_date' => 'date',
        'invoice_tax_summary' => 'array',
    ];

    /**
     * Get the client that owns the invoice.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'Paid';
    }

    /**
     * Check if invoice is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->status === 'Unpaid';
    }

    /**
     * Get bill to data as array.
     */
    public function getBillToAttribute(): array
    {
        return $this->bill_to_json ?? [];
    }

    /**
     * Get the reminder logs for the invoice.
     */
    public function reminderLogs(): HasMany
    {
        return $this->hasMany(InvoiceReminderLog::class);
    }

    /**
     * Get the per-invoice email configuration.
     */
    public function emailConfiguration(): HasOne
    {
        return $this->hasOne(InvoiceEmailConfiguration::class);
    }

    /**
     * Get reminder rule-template bindings for this invoice.
     */
    public function reminderTemplateBindings(): HasMany
    {
        return $this->hasMany(InvoiceReminderTemplateBinding::class);
    }

    /**
     * Get custom reminder schedules for this invoice.
     */
    public function customReminders(): HasMany
    {
        return $this->hasMany(InvoiceCustomReminder::class);
    }
}
