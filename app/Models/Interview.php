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
        'pool_status',
        'claimed_by',
        'claimed_at',
        'priority_level',
        'dh_override',
        'assignment_notes',
        'rating_communication',
        'rating_technical',
        'rating_problem_solving',
        'notes',
        'overall_score',
        'recommendation',
        'rubric_scores',
        'strengths',
        'areas_improvement',
        'overall_rating',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'claimed_at' => 'datetime',
        'dh_override' => 'boolean',
        'rating_communication' => 'integer',
        'rating_technical' => 'integer',
        'rating_problem_solving' => 'integer',
        'overall_score' => 'decimal:2',
        'rubric_scores' => 'array',
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
     * Scope to get available interviews in the pool
     */
    public function scopeAvailableInPool($query)
    {
        return $query->where('status', 'available')
                    ->where('dh_override', false);
    }

    /**
     * Scope to get claimed interviews
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope to get interviews by priority level
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    /**
     * Scope to get high priority interviews
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority_level', 'high');
    }

    /**
     * Scope to get interviews claimed by a specific user
     */
    public function scopeClaimedBy($query, $userId)
    {
        return $query->where('claimed_by', $userId);
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

    /**
     * Interview Pool Management Methods
     */

    /**
     * Claim an interview for a specific user
     */
    public function claimForUser($userId)
    {
        if ($this->status !== 'available') {
            throw new \Exception('Interview is not available for claiming');
        }

        $this->update([
            'status' => 'claimed',
            'claimed_by' => $userId,
            'claimed_at' => now(),
            'interviewer_id' => $userId // Set as interviewer when claimed
        ]);

        return $this;
    }

    /**
     * Release a claimed interview back to the pool
     */
    public function releaseToPool()
    {
        $this->update([
            'status' => 'available',
            'claimed_by' => null,
            'claimed_at' => null,
            'interviewer_id' => null
        ]);

        return $this;
    }

    /**
     * Assign interview to specific instructor (DH override)
     */
    public function assignToInstructor($instructorId, $notes = null)
    {
        $this->update([
            'status' => 'assigned',
            'interviewer_id' => $instructorId,
            'claimed_by' => $instructorId,
            'claimed_at' => now(),
            'dh_override' => true,
            'assignment_notes' => $notes
        ]);

        return $this;
    }

    /**
     * Set priority level
     */
    public function setPriority($priority)
    {
        $this->update(['priority_level' => $priority]);
        return $this;
    }

    /**
     * Check if interview is available for claiming
     */
    public function isAvailableForClaiming()
    {
        return $this->status === 'available' && !$this->dh_override;
    }

    /**
     * Check if interview is claimed by a specific user
     */
    public function isClaimedBy($userId)
    {
        return $this->claimed_by == $userId;
    }

    /**
     * Get time since claimed
     */
    public function getTimeSinceClaimedAttribute()
    {
        if (!$this->claimed_at) {
            return null;
        }

        return $this->claimed_at->diffForHumans();
    }

    /**
     * Check if interview has been claimed too long (for timeout handling)
     */
    public function isClaimedTooLong($hours = 2)
    {
        if (!$this->claimed_at) {
            return false;
        }

        return $this->claimed_at->diffInHours(now()) > $hours;
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute()
    {
        return match($this->priority_level) {
            'high' => 'bg-red-100 text-red-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Static method to add interview to pool after exam completion
     */
    public static function addToPool($applicantId, $priority = 'medium')
    {
        return static::create([
            'applicant_id' => $applicantId,
            'status' => 'available',
            'priority_level' => $priority,
            'dh_override' => false
        ]);
    }
}