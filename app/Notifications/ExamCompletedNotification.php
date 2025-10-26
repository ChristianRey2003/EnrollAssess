<?php

namespace App\Notifications;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExamCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicant;
    protected $score;

    public function __construct(Applicant $applicant, float $score)
    {
        $this->applicant = $applicant;
        $this->score = $score;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'exam_completed',
            'title' => 'Exam Completed',
            'message' => $this->applicant->first_name . ' ' . $this->applicant->last_name . ' completed exam with ' . number_format($this->score, 1) . '%',
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->first_name . ' ' . $this->applicant->last_name,
            'score' => $this->score,
            'url' => route('admin.applicants.show', $this->applicant->id),
            'icon' => 'file-check',
            'created_at' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'exam_completed',
            'title' => 'Exam Completed',
            'message' => $this->applicant->first_name . ' ' . $this->applicant->last_name . ' completed exam',
            'score' => $this->score,
            'url' => route('admin.applicants.show', $this->applicant->id),
        ];
    }
}

