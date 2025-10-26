<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Settings extends Model
{
    protected $table = 'system_settings';
    
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        $cacheKey = 'setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Type casting based on type field
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @return bool
     */
    public static function setSetting($key, $value, $group = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group ?? 'system',
            ]
        );

        // Clear cache
        Cache::forget('setting_' . $key);
        Cache::forget('settings_group_' . $setting->group);
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings in a group
     * 
     * @param string $group
     * @return \Illuminate\Support\Collection
     */
    public static function getGroup($group)
    {
        $cacheKey = 'settings_group_' . $group;
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return self::where('group', $group)->get()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        });
    }

    /**
     * Cast value based on type
     * 
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
            case 'integer':
                return is_numeric($value) ? (int) $value : $value;
            case 'float':
            case 'decimal':
                return is_numeric($value) ? (float) $value : $value;
            case 'json':
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    /**
     * Clear all settings cache
     * 
     * @return void
     */
    public static function clearCache()
    {
        $groups = self::distinct('group')->pluck('group');
        
        foreach ($groups as $group) {
            Cache::forget('settings_group_' . $group);
        }
        
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget('setting_' . $key);
        }
    }
}
