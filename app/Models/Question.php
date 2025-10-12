<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $primaryKey = 'question_id'; // As per ERD

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'correct_answer',
        'points',
        'order_number',
        'explanation',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'order_number' => 'integer',
        'is_active' => 'boolean',
        'correct_answer' => 'boolean',
    ];

    /**
     * Relationships
     */

    /**
     * Get the exam that this question belongs to.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Get the options for this question (for MCQ).
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'question_id')
                    ->orderBy('order_number');
    }

    /**
     * Get the correct option(s) for this question.
     */
    public function correctOptions()
    {
        return $this->options()->where('is_correct', true);
    }

    /**
     * Get the correct option for single-answer questions.
     */
    public function correctOption()
    {
        return $this->hasOne(QuestionOption::class, 'question_id', 'question_id')
                    ->where('is_correct', true);
    }

    /**
     * Get the results for this question.
     */
    public function results()
    {
        return $this->hasMany(Result::class, 'question_id', 'question_id');
    }


    /**
     * Scope to get only active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope queries by question type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('question_type', $type);
    }

    /**
     * Scope to get questions ordered by order_number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number')->orderBy('question_id');
    }

    /**
     * Check if question is multiple choice
     */
    public function isMultipleChoice()
    {
        return $this->question_type === 'multiple_choice';
    }

    /**
     * Check if question is true/false
     */
    public function isTrueFalse()
    {
        return $this->question_type === 'true_false';
    }

    /**
     * Check if question is essay
     */
    public function isEssay()
    {
        return $this->question_type === 'essay';
    }

    /**
     * Get the difficulty level based on correct answer percentage
     */
    public function getDifficultyLevelAttribute()
    {
        $totalResults = $this->results()->count();
        
        if ($totalResults === 0) {
            return 'Unknown';
        }

        $correctResults = $this->results()->where('is_correct', true)->count();
        $correctPercentage = ($correctResults / $totalResults) * 100;

        if ($correctPercentage >= 80) {
            return 'Easy';
        } elseif ($correctPercentage >= 50) {
            return 'Medium';
        } else {
            return 'Hard';
        }
    }

    /**
     * Get statistics for this question
     */
    public function getStatsAttribute()
    {
        $totalAnswers = $this->results()->count();
        $correctAnswers = $this->results()->where('is_correct', true)->count();
        
        return [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $totalAnswers - $correctAnswers,
            'correct_percentage' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0,
        ];
    }

    /**
     * Check answer for this question
     */
    public function checkAnswer($selectedOptionId = null, $answerText = null)
    {
        if ($this->isMultipleChoice() || $this->isTrueFalse()) {
            if (!$selectedOptionId) {
                return false;
            }
            
            $selectedOption = $this->options()->find($selectedOptionId);
            return $selectedOption && $selectedOption->is_correct;
        }

        // For essay questions, manual grading is required
        // This would need to be implemented based on specific requirements
        return null;
    }
}