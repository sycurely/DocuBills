<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminderLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'sent_at',
        'recipient_email',
        'status',
        'reminder_type',
        'rule_id',
        'template_id',
        'status_sent_scope',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'status_sent_scope' => 'date',
    ];

    /**
     * Get the invoice that this reminder was sent for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}
