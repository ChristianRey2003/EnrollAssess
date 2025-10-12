<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

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
        'score',
        'enrollassess_score',
        'interview_score',
        'verbal_description',
        'status',
        'exam_completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'enrollassess_score' => 'decimal:2',
        'interview_score' => 'decimal:2',
        'exam_completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

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
     * Scope queries by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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
     */
    public static function generateApplicationNumber()
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        
        return $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}