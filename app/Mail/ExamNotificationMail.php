<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;
    public $accessCode;
    public $examDate;
    public $examTime;
    public $examVenue;
    public $specialInstructions;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Applicant $applicant, $accessCode, $examDate = null, $examTime = null, $examVenue = null, $specialInstructions = null)
    {
        $this->applicant = $applicant;
        $this->accessCode = $accessCode;
        $this->examDate = $examDate ?? 'To Be Announced';
        $this->examTime = $examTime ?? 'To Be Announced';
        $this->examVenue = $examVenue ?? 'To Be Announced';
        $this->specialInstructions = $specialInstructions;
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
        
        return new Envelope(
            subject: 'BSIT Entrance Exam Notification',
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.exam-notification',
            text: 'emails.exam-notification-text',
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

