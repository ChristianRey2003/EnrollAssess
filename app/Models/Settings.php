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
        'school_year_id',
    ];

    /**
     * Relationship to SchoolYear
     */
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'school_year_id');
    }

    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @param int|null $schoolYearId Optional school year ID for school-year-specific settings
     * @return mixed
     */
    public static function getSetting($key, $default = null, $schoolYearId = null)
    {
        $cacheKey = 'setting_' . $key . ($schoolYearId ? '_sy_' . $schoolYearId : '');
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $schoolYearId) {
            $query = self::where('key', $key);
            
            if ($schoolYearId !== null) {
                // Look for school-year-specific setting first, then fallback to global
                $setting = $query->where('school_year_id', $schoolYearId)->first();
                if (!$setting) {
                    // Fallback to global setting (school_year_id is null)
                    $setting = self::where('key', $key)->whereNull('school_year_id')->first();
                }
            } else {
                // Get global setting (school_year_id is null)
                $setting = $query->whereNull('school_year_id')->first();
            }
            
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
     * @param int|null $schoolYearId Optional school year ID for school-year-specific settings
     * @return bool
     */
    public static function setSetting($key, $value, $group = null, $schoolYearId = null)
    {
        $attributes = [
            'key' => $key,
            'school_year_id' => $schoolYearId,
        ];
        
        $values = [
            'value' => $value,
            'group' => $group ?? 'system',
        ];
        
        $setting = self::updateOrCreate($attributes, $values);

        // Clear cache
        Cache::forget('setting_' . $key);
        Cache::forget('setting_' . $key . ($schoolYearId ? '_sy_' . $schoolYearId : ''));
        Cache::forget('settings_group_' . $setting->group);
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }
    
    /**
     * Get scoring weights for a specific school year
     * 
     * @param int|null $schoolYearId School year ID (null for global/default)
     * @return array
     */
    public static function getScoringWeights($schoolYearId = null)
    {
        return [
            'uee' => (int) self::getSetting('scoring_weight_uee', 60, $schoolYearId),
            'gwa' => (int) self::getSetting('scoring_weight_gwa', 30, $schoolYearId),
            'interview' => (int) self::getSetting('scoring_weight_interview', 5, $schoolYearId),
            'skilltest' => (int) self::getSetting('scoring_weight_skilltest', 5, $schoolYearId),
        ];
    }
    
    /**
     * Set scoring weights for a specific school year
     * 
     * @param array $weights Array with keys: uee, gwa, interview, skilltest
     * @param int|null $schoolYearId School year ID (null for global)
     * @return bool
     */
    public static function setScoringWeights(array $weights, $schoolYearId = null)
    {
        self::setSetting('scoring_weight_uee', $weights['uee'] ?? 60, 'scoring', $schoolYearId);
        self::setSetting('scoring_weight_gwa', $weights['gwa'] ?? 30, 'scoring', $schoolYearId);
        self::setSetting('scoring_weight_interview', $weights['interview'] ?? 5, 'scoring', $schoolYearId);
        self::setSetting('scoring_weight_skilltest', $weights['skilltest'] ?? 5, 'scoring', $schoolYearId);
        
        return true;
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
        
        // Clear all setting caches (including school-year-specific ones)
        $settings = self::select('key', 'school_year_id')->get();
        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
            if ($setting->school_year_id) {
                Cache::forget('setting_' . $setting->key . '_sy_' . $setting->school_year_id);
            }
        }
    }
}
