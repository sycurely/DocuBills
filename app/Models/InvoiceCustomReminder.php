<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceCustomReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'reminder_date',
        'offset_days',
        'offset_base',
        'template_id',
        'status',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
