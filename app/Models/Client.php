<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder): void {
            if (!auth()->check()) {
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
        'company_name',
        'representative',
        'phone',
        'email',
        'address',
        'gst_hst',
        'notes',
        'created_by',
    ];

    /**
     * Get the user that created the client.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoices for the client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the expenses for the client.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
