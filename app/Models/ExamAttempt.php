<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Applicant;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $primaryKey = 'attempt_id';

    protected $fillable = [
        'applicant_id',
        'exam_id',
        'attempt_token',
        'question_ids',
        'started_at',
        'actual_started_at',
        'duration_minutes',
        'answers',
        'current_section',
        'sections_completed',
        'status',
        'violation_count',
        'last_activity_at',
        'completed_at',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'answers' => 'array',
        'sections_completed' => 'array',
        'started_at' => 'datetime',
        'actual_started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'violation_count' => 'integer',
        'current_section' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attempt) {
            if (!$attempt->attempt_token) {
                $attempt->attempt_token = Str::uuid()->toString();
            }
            if (!$attempt->last_activity_at) {
                $attempt->last_activity_at = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeForApplicant($query, $applicantId)
    {
        return $query->where('applicant_id', $applicantId);
    }

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Check if attempt is expired based on duration
     */
    public function isExpired()
    {
        // Use actual_started_at if available, otherwise use started_at
        $startTime = $this->actual_started_at ?? $this->started_at;
        if (!$startTime) {
            return false; // Not started yet
        }
        $endTime = $startTime->copy()->addMinutes($this->duration_minutes);
        return now()->greaterThan($endTime);
    }

    /**
     * Calculate time remaining in seconds
     */
    public function getTimeRemainingAttribute()
    {
        // Use actual_started_at if available, otherwise use started_at
        $startTime = $this->actual_started_at ?? $this->started_at;
        if (!$startTime) {
            // Exam hasn't actually started yet (fullscreen not entered)
            return ($this->duration_minutes ?? 30) * 60;
        }
        $endTime = $startTime->copy()->addMinutes($this->duration_minutes);
        $remaining = now()->diffInSeconds($endTime, false);
        return max(0, $remaining);
    }

    /**
     * Check if attempt can be resumed
     */
    public function canBeResumed()
    {
        // Don't allow resume if applicant has already completed the exam
        if ($this->applicant && $this->applicant->exam_completed_at) {
            return false;
        }
        
        return $this->status === 'in_progress' && !$this->isExpired();
    }

    /**
     * Mark attempt as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Mark attempt as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Mark exam as actually started (when fullscreen is entered)
     */
    public function markAsActuallyStarted()
    {
        if (!$this->actual_started_at) {
            $this->update([
                'actual_started_at' => now(),
                'last_activity_at' => now(),
            ]);
        }
    }

    /**
     * Save answers to attempt
     */
    public function saveAnswers(array $answers)
    {
        $currentAnswers = $this->answers ?? [];
        $mergedAnswers = array_merge($currentAnswers, $answers);
        
        $this->update([
            'answers' => $mergedAnswers,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Get active attempt for applicant and exam
     */
    public static function getActiveAttempt($applicantId, $examId)
    {
        // IMPORTANT: First check if applicant has already completed the exam
        // This prevents resume after exam completion (even if attempt status is still 'in_progress')
        $applicant = Applicant::find($applicantId);
        if ($applicant && $applicant->exam_completed_at) {
            // Applicant has completed exam - mark any in_progress attempts as completed
            static::where('applicant_id', $applicantId)
                ->where('exam_id', $examId)
                ->where('status', 'in_progress')
                ->update([
                    'status' => 'completed',
                    'completed_at' => $applicant->exam_completed_at,
                ]);
            return null; // Don't allow resume if exam is already completed
        }
        
        $attempt = static::with('applicant')
            ->where('applicant_id', $applicantId)
            ->where('exam_id', $examId)
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();
        
        // Check if attempt exists and is not expired
        if ($attempt && $attempt->canBeResumed()) {
            return $attempt;
        }
        
        // If expired, mark it as expired
        if ($attempt && $attempt->isExpired()) {
            $attempt->markAsExpired();
        }
        
        return null;
    }
}
