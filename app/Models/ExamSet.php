<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSet extends Model
{
    use HasFactory;

    protected $primaryKey = 'exam_set_id'; // As per ERD

    protected $fillable = [
        'exam_id',
        'set_name',
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
     * Get the exam that owns this exam set.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Get the questions for this exam set.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_set_id', 'exam_set_id');
    }

    /**
     * Get active questions for this exam set.
     */
    public function activeQuestions()
    {
        return $this->questions()->where('is_active', true)->orderBy('order_number');
    }

    /**
     * Get the applicants assigned to this exam set.
     */
    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'exam_set_id', 'exam_set_id');
    }

    /**
     * Scope to get only active exam sets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total questions count
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->activeQuestions()->count();
    }

    /**
     * Get total points for this exam set
     */
    public function getTotalPointsAttribute()
    {
        return $this->activeQuestions()->sum('points');
    }
}