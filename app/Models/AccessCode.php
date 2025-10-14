<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessCode extends Model
{
    use HasFactory;

    protected $primaryKey = 'code'; // As per ERD
    public $incrementing = false; // Since we're using string as primary key
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'applicant_id',
        'exam_id',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get the applicant that owns this access code.
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the exam assigned to this access code.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Scope to get unused codes
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope to get used codes
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope to get non-expired codes
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if code is valid (not used and not expired)
     */
    public function isValid()
    {
        return !$this->is_used && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Mark code as used
     */
    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Generate a unique access code
     */
    public static function generateUniqueCode($prefix = 'BSIT', $length = 8)
    {
        do {
            $code = $prefix . '-' . strtoupper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate multiple unique codes
     */
    public static function generateMultipleCodes($count, $prefix = 'BSIT', $length = 8)
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = self::generateUniqueCode($prefix, $length);
        }
        
        return $codes;
    }

    /**
     * Create access code for applicant
     */
    public static function createForApplicant($applicantId, $prefix = 'BSIT', $length = 8, $expiresInHours = null)
    {
        $code = self::generateUniqueCode($prefix, $length);
        
        return self::create([
            'code' => $code,
            'applicant_id' => $applicantId,
            'expires_at' => $expiresInHours ? now()->addHours($expiresInHours) : null,
        ]);
    }

    /**
     * Check if exam is assigned to this access code
     */
    public function hasExamAssigned()
    {
        return !is_null($this->exam_id);
    }

    /**
     * Get exam status text for display
     */
    public function getExamStatusText()
    {
        if ($this->hasExamAssigned() && $this->exam) {
            return $this->exam->title;
        }
        
        return 'No exam assigned';
    }
}