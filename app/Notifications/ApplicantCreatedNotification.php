<?php

namespace App\Notifications;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApplicantCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicant;

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'applicant_created',
            'title' => 'New Applicant',
            'message' => 'New applicant registered: ' . $this->applicant->first_name . ' ' . $this->applicant->last_name,
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->first_name . ' ' . $this->applicant->last_name,
            'url' => route('admin.applicants.show', $this->applicant->id),
            'icon' => 'user-plus',
            'created_at' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'applicant_created',
            'title' => 'New Applicant',
            'message' => 'New applicant registered: ' . $this->applicant->first_name . ' ' . $this->applicant->last_name,
            'applicant_id' => $this->applicant->id,
            'url' => route('admin.applicants.show', $this->applicant->id),
        ];
    }
}

