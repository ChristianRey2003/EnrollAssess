<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $interview;

    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'interview_scheduled',
            'title' => 'Interview Scheduled',
            'message' => 'Interview scheduled for ' . $this->interview->applicant->first_name . ' ' . $this->interview->applicant->last_name,
            'interview_id' => $this->interview->id,
            'applicant_name' => $this->interview->applicant->first_name . ' ' . $this->interview->applicant->last_name,
            'scheduled_at' => $this->interview->scheduled_at?->format('M d, Y g:i A'),
            'url' => route('admin.interviews.show', $this->interview->id),
            'icon' => 'calendar',
            'created_at' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'interview_scheduled',
            'title' => 'Interview Scheduled',
            'message' => 'Interview scheduled for ' . $this->interview->applicant->first_name . ' ' . $this->interview->applicant->last_name,
            'url' => route('admin.interviews.show', $this->interview->id),
        ];
    }
}

