<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Tax extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder): void {
            if (!auth()->check() || !Schema::hasColumn('taxes', 'created_by')) {
                return;
            }

            $userIds = workspace_user_ids();
            if ($userIds === []) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $builder->whereIn($builder->qualifyColumn('created_by'), $userIds);
        });
    }

    protected $fillable = [
        'name',
        'percentage',
        'tax_type',
        'calc_order',
        'created_by',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'calc_order' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCurrentUser($query)
    {
        if (!Schema::hasColumn('taxes', 'created_by')) {
            return $query;
        }

        $userIds = workspace_user_ids();

        if ($userIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('created_by', $userIds);
    }

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
