<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public $timestamps = false;

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

            $builder->whereIn($builder->qualifyColumn('user_id'), $userIds);
        });
    }

    protected $fillable = [
        'user_id',
        'username',
        'ip_address',
        'user_agent',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that made the login attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
