<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $primaryKey = 'exam_id'; // As per ERD

    protected $fillable = [
        'title',
        'duration_minutes',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    /**
     * Get the exam sets for this exam.
     */
    public function examSets()
    {
        return $this->hasMany(ExamSet::class, 'exam_id', 'exam_id');
    }

    /**
     * Get active exam sets for this exam.
     */
    public function activeExamSets()
    {
        return $this->examSets()->where('is_active', true);
    }

    /**
     * Scope to get only active exams
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . ' minutes';
    }
}