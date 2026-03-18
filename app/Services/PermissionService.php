<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    /**
     * Check if user has a specific permission.
     */
    public static function hasPermission(User $user, string $permissionName): bool
    {
        return $user->hasPermission($permissionName);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public static function hasAnyPermission(User $user, array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public static function hasAllPermissions(User $user, array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if (!$user->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
}
