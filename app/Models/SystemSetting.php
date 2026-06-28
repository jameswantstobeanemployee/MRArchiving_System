<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'setting_key', 'setting_value', 'setting_type',
        'category', 'description', 'is_editable_by_staff'
    ];

    protected $casts = ['is_editable_by_staff' => 'boolean'];

    public static function getValue(string $key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 300, function () use ($key) {
            return self::where('setting_key', $key)->first();
        });

        if (!$setting) return $default;

        return match($setting->setting_type) {
            'integer' => (int) $setting->setting_value,
            'boolean' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'array'   => explode(',', $setting->setting_value),
            'json'    => json_decode($setting->setting_value, true),
            default   => $setting->setting_value,
        };
    }

    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
        Cache::forget("setting_{$key}");
    }

    public static function getByCategory(string $category): \Illuminate\Support\Collection
    {
        return self::where('category', $category)->get();
    }
}
