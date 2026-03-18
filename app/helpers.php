<?php

use App\Models\User;
use App\Services\PermissionService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Log;

if (!function_exists('has_permission')) {
    /**
     * Check if the authenticated user has a specific permission.
     *
     * @param  string  $permissionName
     * @return bool
     */
    function has_permission(string $permissionName): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return PermissionService::hasPermission($user, $permissionName);
    }
}

if (!function_exists('can_add_client')) {
    /**
     * Check if the authenticated user can add (create) clients.
     * Super_admin and admin always can; others require add_client permission.
     *
     * @return bool
     */
    function can_add_client(): bool
    {
        $user = auth()->user();
        if (!$user) {
            Log::channel('single')->debug('can_add_client: no user', []);
            return false;
        }
        $user->loadMissing('role');
        $isAdmin = $user->isAdminOrSuperAdmin();
        $hasPerm = has_permission('add_client');
        $result = $isAdmin || $hasPerm;
        Log::channel('single')->debug('can_add_client', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->name ?? null,
            'isAdminOrSuperAdmin' => $isAdmin,
            'has_permission_add_client' => $hasPerm,
            'result' => $result,
        ]);
        return $result;
    }
}

if (!function_exists('can_edit_client')) {
    /**
     * Check if the authenticated user can edit clients.
     * Super_admin and admin always can; others require edit_client permission.
     *
     * @return bool
     */
    function can_edit_client(): bool
    {
        $user = auth()->user();
        if (!$user) {
            Log::channel('single')->debug('can_edit_client: no user', []);
            return false;
        }
        $user->loadMissing('role');
        $isAdmin = $user->isAdminOrSuperAdmin();
        $hasPerm = has_permission('edit_client');
        $result = $isAdmin || $hasPerm;
        Log::channel('single')->debug('can_edit_client', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->name ?? null,
            'isAdminOrSuperAdmin' => $isAdmin,
            'has_permission_edit_client' => $hasPerm,
            'result' => $result,
        ]);
        return $result;
    }
}

if (!function_exists('setting')) {
    /**
     * Get a setting value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting(string $key, $default = '')
    {
        return SettingService::get($key, $default);
    }
}

if (!function_exists('has_any_setting_permission')) {
    /**
     * Check if authenticated user can access settings pages based on any settings-related permission.
     */
    function has_any_setting_permission(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $permissions = [
            'access_basic_settings',
            'update_basic_settings',
            'manage_payment_methods',
            'manage_card_payments',
            'manage_bank_details',
            'access_email_templates_page',
            'add_email_template',
            'edit_email_template',
            'delete_email_template',
            'manage_notification_categories',
            'manage_reminder_settings',
            'manage_permissions',
            'manage_users_page',
            'assign_roles',
            'edit_user',
            'suspend_users',
            'manage_role_viewable',
        ];

        return PermissionService::hasAnyPermission($user, $permissions);
    }
}
