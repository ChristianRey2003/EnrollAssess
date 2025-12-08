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
    public $isReminder;

    /**
     * Create a new message instance.
     */
    public function __construct(Applicant $applicant, Interview $interview, $isReminder = false)
    {
        $this->applicant = $applicant;
        $this->interview = $interview;
        $this->instructor = $interview->interviewer;
        $this->isReminder = $isReminder;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', 'EnrollAssess System');
        
        // Ensure we have a from address
        if (empty($fromAddress)) {
            $fromAddress = 'noreply@evsu.edu.ph';
        }

        $subject = $this->isReminder 
            ? 'Reminder: Interview Scheduled - ' . config('app.name')
            : 'Interview Scheduled - ' . config('app.name');
            
        return new Envelope(
            subject: $subject,
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-schedule',
            text: 'emails.interview-schedule-text',
            with: [
                'applicant' => $this->applicant,
                'interview' => $this->interview,
                'instructor' => $this->instructor,
                'scheduleDate' => $this->interview->schedule_date->format('F d, Y'),
                'scheduleTime' => $this->interview->schedule_date->format('g:i A'),
                'isReminder' => $this->isReminder,
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

