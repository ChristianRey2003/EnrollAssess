<?php

namespace App\Mail;

use App\Models\Applicant;
use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;
    public $interview;
    public $instructor;

    /**
     * Create a new message instance.
     */
    public function __construct(Applicant $applicant, Interview $interview)
    {
        $this->applicant = $applicant;
        $this->interview = $interview;
        $this->instructor = $interview->interviewer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Scheduled - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-schedule',
            with: [
                'applicant' => $this->applicant,
                'interview' => $this->interview,
                'instructor' => $this->instructor,
                'scheduleDate' => $this->interview->schedule_date->format('F d, Y'),
                'scheduleTime' => $this->interview->schedule_date->format('g:i A'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

