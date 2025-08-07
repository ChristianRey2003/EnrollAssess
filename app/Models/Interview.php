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
        'rating_communication',
        'rating_technical',
        'rating_problem_solving',
        'notes',
        'overall_score',
        'recommendation',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'rating_communication' => 'integer',
        'rating_technical' => 'integer',
        'rating_problem_solving' => 'integer',
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
     * Calculate overall score from individual ratings
     */
    public function calculateOverallScore()
    {
        $ratings = [
            $this->rating_communication,
            $this->rating_technical,
            $this->rating_problem_solving
        ];

        // Filter out null ratings
        $validRatings = array_filter($ratings, function($rating) {
            return $rating !== null;
        });

        if (empty($validRatings)) {
            return null;
        }

        $average = array_sum($validRatings) / count($validRatings);
        
        $this->update(['overall_score' => round($average, 2)]);
        
        return $this->overall_score;
    }

    /**
     * Auto-generate recommendation based on overall score
     */
    public function generateRecommendation()
    {
        if ($this->overall_score === null) {
            return null;
        }

        if ($this->overall_score >= 80) {
            return 'recommended';
        } elseif ($this->overall_score >= 70) {
            return 'waitlisted';
        } else {
            return 'not-recommended';
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