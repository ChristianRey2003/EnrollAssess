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
        'full_name',
        'email_address',
        'phone_number',
        'address',
        'education_background',
        'exam_set_id',
        'score',
        'status',
        'exam_completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'exam_completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get the exam set assigned to this applicant.
     */
    public function examSet()
    {
        return $this->belongsTo(ExamSet::class, 'exam_set_id', 'exam_set_id');
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
        if (!$this->score || !$this->examSet) {
            return 0;
        }

        $totalPoints = $this->examSet->total_points;
        return $totalPoints > 0 ? round(($this->score / $totalPoints) * 100, 2) : 0;
    }

    /**
     * Get applicant initials
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->full_name);
        $initials = '';
        
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper($name[0]);
            }
        }
        
        return $initials;
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