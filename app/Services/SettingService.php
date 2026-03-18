<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get a setting value.
     */
    public static function get(string $key, $default = '')
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::get($key, $default);
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
        Setting::set($key, $value);
        Cache::forget("setting.{$key}");
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
