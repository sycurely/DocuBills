<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key_name',
        'key_value',
        'admin_email',
        'invoice_prefix',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get or set a setting value.
     */
    public static function get(string $key, $default = '')
    {
        $setting = self::where('key_name', $key)->first();
        return $setting ? $setting->key_value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        if ($value === null) {
            $value = '';
        }

        self::updateOrCreate(
            ['key_name' => $key],
            ['key_value' => $value, 'updated_at' => now()]
        );
    }
}
