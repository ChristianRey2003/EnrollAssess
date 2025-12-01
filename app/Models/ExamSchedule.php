<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $primaryKey = 'exam_schedule_id';

    protected $fillable = [
        'applicant_id',
        'scheduled_date',
        'scheduled_time',
        'venue',
        'special_instructions',
        'scheduled_by',
        'status',
        'notification_sent',
        'notification_sent_at',
        'reschedule_count',
        'previous_schedule_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'reschedule_count' => 'integer',
    ];

    /**
     * Relationships
     */
    
    /**
     * Get the applicant for this schedule
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the user who scheduled this exam
     */
    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by', 'user_id');
    }

    /**
     * Get the previous schedule if this is a reschedule
     */
    public function previousSchedule()
    {
        return $this->belongsTo(ExamSchedule::class, 'previous_schedule_id', 'exam_schedule_id');
    }

    /**
     * Get rescheduled schedules (schedules that were created from this one)
     */
    public function rescheduledSchedules()
    {
        return $this->hasMany(ExamSchedule::class, 'previous_schedule_id', 'exam_schedule_id');
    }

    /**
     * Scopes
     */
    
    /**
     * Scope to get scheduled exams
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_date', '>=', now()->toDateString());
    }

    /**
     * Scope to get past schedules
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_date', '<', now()->toDateString());
    }

    /**
     * Scope to get schedules that need no-show detection
     */
    public function scopeNeedsNoShowDetection($query)
    {
        $yesterday = now()->subDay()->toDateString();
        return $query->where('status', 'scheduled')
                    ->where('scheduled_date', '<=', $yesterday)
                    ->whereDoesntHave('applicant', function($q) {
                        $q->where('status', 'exam-completed');
                    });
    }

    /**
     * Helper Methods
     */
    
    /**
     * Get formatted scheduled datetime
     */
    public function getFormattedDateTimeAttribute()
    {
        if (!$this->scheduled_date) {
            return 'Not scheduled';
        }
        $date = $this->scheduled_date->format('M d, Y');
        $time = Carbon::parse($this->scheduled_time)->format('g:i A');
        return "{$date} at {$time}";
    }

    /**
     * Get full scheduled datetime as Carbon instance
     */
    public function getScheduledDateTimeAttribute()
    {
        return Carbon::parse($this->scheduled_date->format('Y-m-d') . ' ' . $this->scheduled_time);
    }

    /**
     * Check if schedule is in the past
     */
    public function isPast()
    {
        return $this->scheduled_date < now()->toDateString();
    }

    /**
     * Check if schedule is today
     */
    public function isToday()
    {
        return $this->scheduled_date->isToday();
    }

    /**
     * Check if schedule is upcoming
     */
    public function isUpcoming()
    {
        return $this->scheduled_date > now()->toDateString();
    }

    /**
     * Mark as no-show
     */
    public function markAsNoShow()
    {
        $this->update(['status' => 'no-show']);
        
        // Revert applicant status to pending
        if ($this->applicant) {
            $this->applicant->update(['status' => 'pending']);
        }
    }

    /**
     * Mark notification as sent
     */
    public function markNotificationSent()
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }
}
