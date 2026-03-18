<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'full_name',
        'password',
        'role_id',
        'is_suspended',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permissions for the user through their role.
     */
    public function permissions()
    {
        // Check if user has a role_id before loading relationship
        if (!$this->role_id) {
            return collect();
        }

        // Load role relationship if not already loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        $role = $this->role;
        
        // Ensure we have a Role model instance, not a string or null
        if (!$role || !($role instanceof Role)) {
            return collect();
        }

        return $role->permissions ?? collect();
    }

    /**
     * Get the user sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get the login logs for the user.
     */
    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        // Check if user has a role_id before loading relationship
        if (!$this->role_id) {
            return false;
        }

        // Load role relationship if not already loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        $role = $this->role;
        
        // Ensure we have a Role model instance, not a string or null
        if (!$role || !($role instanceof Role)) {
            return false;
        }

        // Unrestricted access: admin and super_admin are treated as having all permissions
        if (in_array($role->name, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $role->permissions()
            ->where('name', $permissionName)
            ->exists();
    }

    /**
     * Check if the user has the super_admin role (full system access).
     */
    public function isSuperAdmin(): bool
    {
        $this->loadMissing('role');
        return $this->role && $this->role->name === 'super_admin';
    }

    /**
     * Check if the user has admin or super_admin role (full system access).
     */
    public function isAdminOrSuperAdmin(): bool
    {
        $this->loadMissing('role');
        $name = $this->role?->name ?? null;
        $result = $name && in_array($name, ['admin', 'super_admin'], true);
        if (!$result && app()->has('log')) {
            Log::channel('single')->debug('User::isAdminOrSuperAdmin false', [
                'user_id' => $this->id,
                'username' => $this->username ?? null,
                'role_id' => $this->role_id,
                'role_name' => $name,
                'role_loaded' => $this->relationLoaded('role'),
            ]);
        }
        return $result;
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended === true;
    }
}
