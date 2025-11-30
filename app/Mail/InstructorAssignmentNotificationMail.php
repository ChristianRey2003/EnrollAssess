<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InstructorAssignmentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $instructor;
    public $applicants;
    public $interviewStartDate;
    public $interviewEndDate;
    public $assignmentMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(User $instructor, Collection $applicants, $interviewStartDate, $interviewEndDate, $assignmentMessage = null)
    {
        $this->instructor = $instructor;
        $this->applicants = $applicants;
        $this->interviewStartDate = $interviewStartDate;
        $this->interviewEndDate = $interviewEndDate;
        $this->assignmentMessage = $assignmentMessage;
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

        $applicantCount = $this->applicants->count();
        $subject = $applicantCount === 1 
            ? 'New Applicant Assigned for Interview'
            : "{$applicantCount} Applicants Assigned for Interview";

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
            view: 'emails.instructor-assignment-notification',
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

