<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEmailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'delivery_template_id',
        'payment_confirmation_template_id',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deliveryTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'delivery_template_id');
    }

    public function paymentConfirmationTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'payment_confirmation_template_id');
    }
}

