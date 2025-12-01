<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'applicant_id'; // As per ERD

    protected $fillable = [
        'application_no',
        'first_name',
        'middle_name', 
        'last_name',
        'preferred_course',
        'email_address',
        'phone_number',
        'assigned_instructor_id',
        'school_year_id',
        'score',
        'enrollassess_score',
        'interview_score',
        'card_tor_gwa',
        'verbal_description',
        'status',
        'exam_completed_at',
        'violation_count',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'enrollassess_score' => 'decimal:2',
        'interview_score' => 'decimal:2',
        'card_tor_gwa' => 'decimal:2',
        'exam_completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get the school year for this applicant.
     */
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'school_year_id');
    }

    /**
     * Get the assigned instructor for this applicant.
     */
    public function assignedInstructor()
    {
        return $this->belongsTo(User::class, 'assigned_instructor_id', 'user_id');
    }

    /**
     * Get the access code for this applicant.
     */
    public function accessCode()
    {
        return $this->hasOne(AccessCode::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the interviews for this applicant.
     */
    public function interviews()
    {
        return $this->hasMany(Interview::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the latest interview for this applicant.
     */
    public function latestInterview()
    {
        return $this->hasOne(Interview::class, 'applicant_id', 'applicant_id')->latest();
    }

    /**
     * Get the exam results for this applicant.
     */
    public function results()
    {
        return $this->hasMany(Result::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the exam schedules for this applicant.
     */
    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the latest exam schedule for this applicant.
     */
    public function latestExamSchedule()
    {
        return $this->hasOne(ExamSchedule::class, 'applicant_id', 'applicant_id')
                    ->where('status', 'scheduled')
                    ->latest();
    }

    /**
     * Get the basic information for this applicant.
     */
    public function basicInfo()
    {
        return $this->hasOne(ApplicantBasicInfo::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Check if applicant has completed basic information.
     */
    public function hasCompletedBasicInfo(): bool
    {
        return $this->basicInfo && $this->basicInfo->completed_at !== null;
    }


    /**
     * Scope queries by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by school year
     */
    public function scopeForSchoolYear($query, $schoolYearId)
    {
        if ($schoolYearId) {
            return $query->where('school_year_id', $schoolYearId);
        }
        return $query;
    }

    /**
     * Scope to filter by current school year from session
     */
    public function scopeForCurrentSchoolYear($query)
    {
        $schoolYearId = session('school_year_id');
        if ($schoolYearId) {
            return $query->where('school_year_id', $schoolYearId);
        }
        return $query;
    }

    /**
     * Scope to get applicants who completed exam
     */
    public function scopeExamCompleted($query)
    {
        return $query->where('status', 'exam-completed')
                    ->orWhere('status', 'interview-scheduled')
                    ->orWhere('status', 'interview-completed')
                    ->orWhere('status', 'admitted')
                    ->orWhere('status', 'rejected');
    }

    /**
     * Check if applicant has completed exam
     */
    public function hasCompletedExam()
    {
        return in_array($this->status, [
            'exam-completed', 
            'interview-scheduled', 
            'interview-completed', 
            'admitted', 
            'rejected'
        ]);
    }

    /**
     * Get exam percentage score
     */
    public function getExamPercentageAttribute()
    {
        if (!$this->enrollassess_score) {
            return 0;
        }

        // EnrollAssess score is already a percentage (0-100)
        return round($this->enrollassess_score, 2);
    }

    /**
     * Get full name from individual name components
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        return implode(' ', $parts);
    }

    /**
     * Get applicant initials
     */
    public function getInitialsAttribute()
    {
        $initials = '';
        
        if ($this->first_name) {
            $initials .= strtoupper($this->first_name[0]);
        }
        if ($this->middle_name) {
            $initials .= strtoupper($this->middle_name[0]);
        }
        if ($this->last_name) {
            $initials .= strtoupper($this->last_name[0]);
        }
        
        // Fallback if no initials could be generated
        if (empty($initials)) {
            $initials = 'N/A';
        }
        
        return $initials;
    }

    /**
     * Get weighted exam percentage (60% of exam score)
     */
    public function getWeightedExamPercentageAttribute()
    {
        if (!$this->score) {
            return 0;
        }
        
        return round($this->exam_percentage * 0.6, 2);
    }

    /**
     * Get verbal description based on exam percentage
     */
    public function getComputedVerbalDescriptionAttribute()
    {
        // Return stored verbal description if available
        if ($this->verbal_description) {
            return $this->verbal_description;
        }
        
        // Compute from exam percentage
        $percentage = $this->exam_percentage;
        
        if ($percentage >= 95) return 'Excellent';
        if ($percentage >= 85) return 'Very Good';
        if ($percentage >= 75) return 'Good';
        if ($percentage >= 65) return 'Satisfactory';
        if ($percentage >= 50) return 'Fair';
        return 'Needs Improvement';
    }

    /**
     * Get formatted applicant number for display
     */
    public function getFormattedApplicantNoAttribute()
    {
        // Format like: 0-25-1-08946-1806
        if (!$this->applicant_id) {
            return $this->application_no;
        }
        
        // Extract components for formatting
        $year = date('y'); // 2-digit year
        $month = date('n'); // Month without leading zeros
        $sequence = str_pad($this->applicant_id, 5, '0', STR_PAD_LEFT);
        $checksum = str_pad(($this->applicant_id % 10000), 4, '0', STR_PAD_LEFT);
        
        return "0-{$year}-{$month}-{$sequence}-{$checksum}";
    }

    /**
     * Generate unique application number
     * Format: 0-25-11-00314-0314
     * Where: 0-prefix, 25-year, 11-month, 00314-sequence, 0314-checksum
     */
    public static function generateApplicationNumber()
    {
        // Get the next applicant_id (max + 1 or 1 if no records)
        $maxId = self::withTrashed()->max('applicant_id');
        $nextId = $maxId ? $maxId + 1 : 1;
        
        // Format components
        $year = date('y'); // 2-digit year (25 for 2025)
        $month = date('m'); // Month with leading zero (11 for November)
        $sequence = str_pad($nextId, 5, '0', STR_PAD_LEFT); // 5-digit sequence
        $checksum = str_pad(($nextId % 10000), 4, '0', STR_PAD_LEFT); // 4-digit checksum
        
        $applicationNo = "0-{$year}-{$month}-{$sequence}-{$checksum}";
        
        // Ensure uniqueness (in case of race conditions or manual entries)
        $attempts = 0;
        while (self::where('application_no', $applicationNo)->exists() && $attempts < 100) {
            $nextId++;
            $sequence = str_pad($nextId, 5, '0', STR_PAD_LEFT);
            $checksum = str_pad(($nextId % 10000), 4, '0', STR_PAD_LEFT);
            $applicationNo = "0-{$year}-{$month}-{$sequence}-{$checksum}";
            $attempts++;
        }
        
        return $applicationNo;
    }

    /**
     * Get overall admission rating
     * Uses AdmissionScoringService to calculate 60/30/10 weighted score
     * 
     * @return array|null
     */
    public function getOverallRating(): ?array
    {
        $scoringService = app(\App\Services\AdmissionScoringService::class);
        
        if (!$scoringService->hasAllRequiredScores($this)) {
            return null;
        }
        
        return $scoringService->calculateOverallRating($this);
    }

    /**
     * Get overall rating value only
     * 
     * @return float|null
     */
    public function getOverallRatingValueAttribute(): ?float
    {
        $rating = $this->getOverallRating();
        return $rating ? $rating['overall_rating'] : null;
    }

    /**
     * Check if applicant has all required scores for overall rating
     * 
     * @return bool
     */
    public function hasAllRequiredScores(): bool
    {
        $scoringService = app(\App\Services\AdmissionScoringService::class);
        return $scoringService->hasAllRequiredScores($this);
    }

    /**
     * Get missing score components
     * 
     * @return array
     */
    public function getMissingScores(): array
    {
        $scoringService = app(\App\Services\AdmissionScoringService::class);
        return $scoringService->getMissingScores($this);
    }
}