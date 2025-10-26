<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'title',
        'file_path',
        'filters_applied',
        'generated_by',
        'file_size',
        'status',
        'metadata',
    ];

    protected $casts = [
        'filters_applied' => 'array',
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Get the user who generated this report.
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get human-readable report type.
     */
    public function getReadableTypeAttribute()
    {
        $types = [
            'final_ranking' => 'Final Applicant Ranking',
            'statistical_analysis' => 'Statistical Analysis',
            'interview_summary' => 'Interview Summary',
            'question_analytics' => 'Question Analytics',
            'communication_log' => 'Communication Log',
            'security_audit' => 'Security Audit',
            'timing_analysis' => 'Timing Analysis',
        ];

        return $types[$this->report_type] ?? ucwords(str_replace('_', ' ', $this->report_type));
    }

    /**
     * Scope to get reports by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to get reports by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('generated_by', $userId);
    }

    /**
     * Check if file exists.
     */
    public function fileExists()
    {
        return file_exists(storage_path('app/' . $this->file_path));
    }
}
