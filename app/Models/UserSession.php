<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
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
        'session_id',
        'ip_address',
        'last_activity',
        'terminated_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
