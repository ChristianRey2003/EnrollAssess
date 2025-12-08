<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantBasicInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'sex',
        'date_of_birth',
        'age',
        'civil_status',
        'applicant_type',
        'is_pwd',
        'complete_address',
        'city_municipality',
        'province',
        'senior_high_school_strand',
        'senior_high_school_strand_other',
        'senior_high_school_name',
        'facebook_link',
        'completed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'completed_at' => 'datetime',
        'age' => 'integer',
    ];

    /**
     * Get the applicant that owns this basic info.
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Check if basic info is completed.
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(): void
    {
        $this->completed_at = now();
        $this->save();
    }
}

