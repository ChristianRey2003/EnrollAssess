<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        $endTime = $this->started_at->copy()->addMinutes($this->duration_minutes);
        return now()->greaterThan($endTime);
    }

    /**
     * Calculate time remaining in seconds
     */
    public function getTimeRemainingAttribute()
    {
        $endTime = $this->started_at->copy()->addMinutes($this->duration_minutes);
        $remaining = now()->diffInSeconds($endTime, false);
        return max(0, $remaining);
    }

    /**
     * Check if attempt can be resumed
     */
    public function canBeResumed()
    {
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
        $attempt = static::where('applicant_id', $applicantId)
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
