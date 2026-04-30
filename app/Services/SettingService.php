<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private static function scopedKey(string $key): string
    {
        if (!auth()->check()) {
            return $key;
        }

        $ownerId = current_workspace_owner_id();
        return $ownerId ? "workspace_{$ownerId}.{$key}" : $key;
    }

    /**
     * Get a setting value.
     */
    public static function get(string $key, $default = '')
    {
        $scopedKey = self::scopedKey($key);

        return Cache::remember("setting.{$scopedKey}", 3600, function () use ($scopedKey, $default) {
            return Setting::get($scopedKey, $default);
        });
    }

    /**
     * Alias for get() method for backward compatibility.
     */
    public static function getSetting(string $key, $default = '')
    {
        return self::get($key, $default);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        $scopedKey = self::scopedKey($key);

        Setting::set($scopedKey, $value);
        Cache::forget("setting.{$scopedKey}");
    }

    /**
     * Get multiple settings at once.
     */
    public static function getMany(array $keys): array
    {
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = self::get($key);
        }
        return $settings;
    }

    /**
     * Set multiple settings at once.
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }
}
