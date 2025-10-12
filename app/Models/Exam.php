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
        'total_items',
        'mcq_quota',
        'tf_quota',
        'description',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get all questions in the question bank for this exam.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_id', 'exam_id');
    }

    /**
     * Get active questions for this exam.
     */
    public function activeQuestions()
    {
        return $this->questions()->where('is_active', true);
    }

    /**
     * Get multiple choice questions.
     */
    public function multipleChoiceQuestions()
    {
        return $this->activeQuestions()->where('question_type', 'multiple_choice');
    }

    /**
     * Get true/false questions.
     */
    public function trueFalseQuestions()
    {
        return $this->activeQuestions()->where('question_type', 'true_false');
    }

    /**
     * Get applicants assigned to this exam.
     */
    public function applicants()
    {
        return $this->hasManyThrough(
            Applicant::class,
            Result::class,
            'exam_id', // Foreign key on results table
            'applicant_id', // Foreign key on applicants table
            'exam_id', // Local key on exams table
            'applicant_id' // Local key on results table
        )->distinct();
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

    /**
     * Get total questions count in question bank
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->activeQuestions()->count();
    }

    /**
     * Get MCQ count
     */
    public function getMcqCountAttribute()
    {
        return $this->multipleChoiceQuestions()->count();
    }

    /**
     * Get T/F count
     */
    public function getTfCountAttribute()
    {
        return $this->trueFalseQuestions()->count();
    }

    /**
     * Check if exam has enough questions for quotas
     */
    public function hasEnoughQuestions()
    {
        $mcqAvailable = $this->mcq_count;
        $tfAvailable = $this->tf_count;

        return $mcqAvailable >= ($this->mcq_quota ?? 0) && 
               $tfAvailable >= ($this->tf_quota ?? 0);
    }

    /**
     * Validate quotas against available questions
     */
    public function validateQuotas()
    {
        $errors = [];

        if ($this->mcq_quota && $this->mcq_count < $this->mcq_quota) {
            $errors[] = "Not enough MCQ questions. Available: {$this->mcq_count}, Required: {$this->mcq_quota}";
        }

        if ($this->tf_quota && $this->tf_count < $this->tf_quota) {
            $errors[] = "Not enough T/F questions. Available: {$this->tf_count}, Required: {$this->tf_quota}";
        }

        $totalQuota = ($this->mcq_quota ?? 0) + ($this->tf_quota ?? 0);
        if ($totalQuota != $this->total_items) {
            $errors[] = "Total quotas ({$totalQuota}) must equal total items ({$this->total_items})";
        }

        return $errors;
    }
}