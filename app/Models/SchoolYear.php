<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $primaryKey = 'school_year_id';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all applicants for this school year
     */
    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'school_year_id', 'school_year_id');
    }

    /**
     * Scope to get active school years
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get current school year
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Get the current school year
     */
    public static function getCurrent()
    {
        return static::where('is_current', true)
                     ->where('is_active', true)
                     ->first();
    }

    /**
     * Set this school year as current (and unset others)
     */
    public function setAsCurrent()
    {
        // Unset all other current years
        static::where('school_year_id', '!=', $this->school_year_id)
              ->update(['is_current' => false]);
        
        // Set this as current
        $this->update(['is_current' => true, 'is_active' => true]);
    }
}
