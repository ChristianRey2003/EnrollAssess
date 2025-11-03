<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $primaryKey = 'interview_id'; // As per ERD

    protected $fillable = [
        'applicant_id',
        'interviewer_id',
        'schedule_date',
        'status',
        // BSIT Rubric Criteria (8 criteria, 10 points each)
        'communication_skills',
        'motivation_interest',
        'problem_solving_attitude',
        'program_understanding',
        'personality_attitude',
        'it_background',
        'willingness_to_learn',
        'overall_impression',
        // Scores and Assessment
        'overall_score',
        'overall_rating',
        'recommendation',
        // Written Feedback
        'strengths',
        'areas_improvement',
        'interview_notes',
        'evaluator_notes',
        'final_comments',
        // Pool Management
        'claimed_by',
        'claimed_at',
        // Assignment and Deadlines
        'assignment_notes',
        'interview_deadline_start',
        'interview_deadline_end',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'claimed_at' => 'datetime',
        'interview_deadline_start' => 'datetime',
        'interview_deadline_end' => 'datetime',
        // BSIT Rubric Criteria
        'communication_skills' => 'integer',
        'motivation_interest' => 'integer',
        'problem_solving_attitude' => 'integer',
        'program_understanding' => 'integer',
        'personality_attitude' => 'integer',
        'it_background' => 'integer',
        'willingness_to_learn' => 'integer',
        'overall_impression' => 'integer',
        // Overall Score
        'overall_score' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    /**
     * Get the applicant that this interview belongs to.
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the interviewer (user) that conducts this interview.
     */
    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id', 'user_id');
    }

    /**
     * Get the user who claimed this interview.
     */
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by', 'user_id');
    }

    /**
     * Scope queries by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get scheduled interviews
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get completed interviews
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get interviews by interviewer
     */
    public function scopeByInterviewer($query, $interviewerId)
    {
        return $query->where('interviewer_id', $interviewerId);
    }

    /**
     * Scope to get claimed interviews
     */
    public function scopeClaimed($query)
    {
        return $query->whereNotNull('claimed_by');
    }

    /**
     * Scope to get available interviews
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('claimed_by');
    }

    /**
     * Calculate overall score from BSIT rubric criteria (sum of 8 criteria = 80 points)
     * Plus recommendation score (0-20 points) = Total 100 points
     */
    public function calculateOverallScore()
    {
        $criteria = [
            $this->communication_skills,
            $this->motivation_interest,
            $this->problem_solving_attitude,
            $this->program_understanding,
            $this->personality_attitude,
            $this->it_background,
            $this->willingness_to_learn,
            $this->overall_impression
        ];

        // Filter out null values
        $validCriteria = array_filter($criteria, function($score) {
            return $score !== null;
        });

        if (empty($validCriteria)) {
            return null;
        }

        // Sum of all 8 criteria (max 80 points)
        $criteriaScore = array_sum($validCriteria);
        
        // Add recommendation score
        $recommendationScore = $this->getRecommendationScore();
        
        $totalScore = $criteriaScore + $recommendationScore;
        
        $this->update(['overall_score' => $totalScore]);
        
        return $this->overall_score;
    }

    /**
     * Get recommendation score based on recommendation level
     */
    public function getRecommendationScore()
    {
        switch ($this->recommendation) {
            case 'highly_recommended':
                return 20;
            case 'recommended':
                return 10;
            case 'conditional':
                return 5;
            case 'not_recommended':
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Check if interview score is passing (>= 50 out of 100)
     */
    public function isPassing()
    {
        return $this->overall_score !== null && $this->overall_score >= 50;
    }

    /**
     * Auto-generate recommendation based on overall score (100 point scale)
     */
    public function generateRecommendation()
    {
        if ($this->overall_score === null) {
            return null;
        }

        if ($this->overall_score >= 85) {
            return 'highly_recommended';
        } elseif ($this->overall_score >= 70) {
            return 'recommended';
        } elseif ($this->overall_score >= 50) {
            return 'conditional';
        } else {
            return 'not_recommended';
        }
    }

    /**
     * Get formatted schedule date
     */
    public function getFormattedScheduleDateAttribute()
    {
        return $this->schedule_date ? $this->schedule_date->format('M d, Y - g:i A') : null;
    }

    /**
     * Check if interview is upcoming
     */
    public function isUpcoming()
    {
        return $this->status === 'scheduled' && 
               $this->schedule_date && 
               $this->schedule_date->isFuture();
    }

    /**
     * Check if interview is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'scheduled' && 
               $this->schedule_date && 
               $this->schedule_date->isPast();
    }

    /**
     * Check if interview has been claimed for too long
     */
    public function isClaimedTooLong($hours = 1)
    {
        if (!$this->claimed_at) {
            return false;
        }
        
        return $this->claimed_at->lt(now()->subHours($hours));
    }

    /**
     * Mark interview as completed and update applicant status
     */
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
        
        // Update applicant status
        $this->applicant->update(['status' => 'interview-completed']);
        
        // Calculate overall score if ratings are provided
        $this->calculateOverallScore();
    }
}