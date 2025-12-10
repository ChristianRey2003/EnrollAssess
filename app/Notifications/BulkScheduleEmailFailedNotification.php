<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BulkScheduleEmailFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $scheduledCount;
    protected $emailsSent;
    protected $emailsFailed;
    protected $emailsFailedCount;

    public function __construct($scheduledCount, $emailsSent, $emailsFailed, $emailsFailedCount)
    {
        $this->scheduledCount = $scheduledCount;
        $this->emailsSent = $emailsSent;
        $this->emailsFailed = $emailsFailed;
        $this->emailsFailedCount = $emailsFailedCount;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $failedNames = implode(', ', $this->emailsFailed);
        $message = "Successfully scheduled {$this->scheduledCount} interview(s). {$this->emailsSent} email(s) sent successfully, {$this->emailsFailedCount} failed: {$failedNames}";
        
        return [
            'type' => 'bulk_schedule_email_failed',
            'title' => 'Bulk Schedule Completed',
            'message' => $message,
            'scheduled_count' => $this->scheduledCount,
            'emails_sent' => $this->emailsSent,
            'emails_failed' => $this->emailsFailed,
            'emails_failed_count' => $this->emailsFailedCount,
            'url' => route('instructor.applicants'),
            'icon' => 'exclamation-triangle',
            'created_at' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        $failedNames = implode(', ', $this->emailsFailed);
        $message = "Successfully scheduled {$this->scheduledCount} interview(s). {$this->emailsSent} email(s) sent successfully, {$this->emailsFailedCount} failed: {$failedNames}";
        
        return [
            'type' => 'bulk_schedule_email_failed',
            'title' => 'Bulk Schedule Completed',
            'message' => $message,
            'url' => route('instructor.applicants'),
        ];
    }
}

