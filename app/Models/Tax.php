<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'name',
        'percentage',
        'tax_type',
        'calc_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'calc_order' => 'integer',
    ];

    /**
     * Scope for line-level taxes.
     */
    public function scopeLineLevel($query)
    {
        return $query->where('tax_type', 'line');
    }

    /**
     * Scope for invoice-level taxes.
     */
    public function scopeInvoiceLevel($query)
    {
        return $query->where('tax_type', 'invoice');
    }

    /**
     * Scope ordered by calculation order for invoice taxes.
     */
    public function scopeOrderedByCalcOrder($query)
    {
        return $query->orderBy('calc_order')->orderBy('id');
    }
}
