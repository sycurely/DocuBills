<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTemplate extends Model
{
    use HasFactory, SoftDeletes;

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
        'name',
        'html',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
