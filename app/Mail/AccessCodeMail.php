<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Applicant;
use App\Models\AccessCode;

class AccessCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $applicant;
    public $accessCode;

    /**
     * Create a new message instance.
     */
    public function __construct(Applicant $applicant, AccessCode $accessCode)
    {
        $this->applicant = $applicant;
        $this->accessCode = $accessCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your EnrollAssess Exam Access Code - Computer Studies Department',
            from: config('mail.from.address', 'noreply@evsu.edu.ph'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.access-code',
            with: [
                'applicant' => $this->applicant,
                'accessCode' => $this->accessCode,
                'examUrl' => route('applicant.login'),
                'expiresAt' => $this->accessCode->expires_at,
            ]
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
