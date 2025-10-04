<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $primaryKey = 'result_id'; // As per ERD

    protected $fillable = [
        'applicant_id',
        'question_id',
        'answer_text',
        'selected_option_id',
        'is_correct',
        'points_earned',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'answered_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get the applicant that this result belongs to.
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the question that this result answers.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    /**
     * Get the selected option (for multiple choice questions).
     */
    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id', 'option_id');
    }

    /**
     * Scope to get only correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get only incorrect answers
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope to get results by applicant
     */
    public function scopeByApplicant($query, $applicantId)
    {
        return $query->where('applicant_id', $applicantId);
    }

    /**
     * Scope to get results by question
     */
    public function scopeByQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Auto-check answer when result is created/updated
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($result) {
            // Ensure answered_at is always set when creating a result
            if (!$result->answered_at) {
                $result->answered_at = now();
            }
        });

        static::saving(function ($result) {
            if ($result->question) {
                $isCorrect = $result->question->checkAnswer(
                    $result->selected_option_id,
                    $result->answer_text
                );

                if ($isCorrect !== null) {
                    $result->is_correct = $isCorrect;
                    $result->points_earned = $isCorrect ? $result->question->points : 0;
                }
            }
        });
    }

    /**
     * Get the display answer for this result
     */
    public function getDisplayAnswerAttribute()
    {
        if ($this->selectedOption) {
            return $this->selectedOption->formatted_option;
        }

        if ($this->answer_text) {
            return $this->answer_text;
        }

        return 'No answer provided';
    }

    /**
     * Get the correct answer for comparison
     */
    public function getCorrectAnswerAttribute()
    {
        if ($this->question->isMultipleChoice() || $this->question->isTrueFalse()) {
            $correctOption = $this->question->correctOption;
            return $correctOption ? $correctOption->formatted_option : 'Not defined';
        }

        return 'Manual grading required';
    }

    /**
     * Calculate exam score for an applicant
     */
    public static function calculateExamScore($applicantId)
    {
        $results = self::where('applicant_id', $applicantId)->get();
        
        $totalPoints = $results->sum(function ($result) {
            return $result->question->points ?? 0;
        });

        $earnedPoints = $results->sum('points_earned');

        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    }

    /**
     * Get exam statistics for an applicant
     */
    public static function getExamStats($applicantId)
    {
        $results = self::where('applicant_id', $applicantId)->get();
        
        $totalQuestions = $results->count();
        $correctAnswers = $results->where('is_correct', true)->count();
        $incorrectAnswers = $results->where('is_correct', false)->count();
        $unanswered = $results->where('is_correct', null)->count();

        $totalPoints = $results->sum(function ($result) {
            return $result->question->points ?? 0;
        });

        $earnedPoints = $results->sum('points_earned');
        $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        return [
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $incorrectAnswers,
            'unanswered' => $unanswered,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'percentage' => $percentage,
            'passing_grade' => 75, // This could be configurable
            'passed' => $percentage >= 75,
        ];
    }

    /**
     * Get category-wise performance for an applicant
     */
    public static function getCategoryPerformance($applicantId)
    {
        // This would need to be implemented based on how questions are categorized
        // For now, we'll return a basic structure
        $results = self::where('applicant_id', $applicantId)
            ->with(['question', 'question.examSet'])
            ->get();

        // Group by exam set or implement category logic
        $categories = $results->groupBy(function ($result) {
            return $result->question->examSet->set_name ?? 'General';
        });

        $performance = [];

        foreach ($categories as $categoryName => $categoryResults) {
            $total = $categoryResults->count();
            $correct = $categoryResults->where('is_correct', true)->count();
            $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            $performance[] = [
                'name' => $categoryName,
                'correct' => $correct,
                'total' => $total,
                'score' => $percentage,
            ];
        }

        return $performance;
    }
}