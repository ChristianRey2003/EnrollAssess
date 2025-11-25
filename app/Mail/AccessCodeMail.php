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
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', 'EnrollAssess System');
        
        // Ensure we have a from address
        if (empty($fromAddress)) {
            $fromAddress = 'noreply@evsu.edu.ph';
        }
        
        return new Envelope(
            subject: 'Your EnrollAssess Exam Access Code - Computer Studies Department',
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.access-code',
            text: 'emails.access-code-text',
            with: [
                'applicant' => $this->applicant,
                'accessCode' => $this->accessCode,
                'examUrl' => 'https://enrollassess-evsu.com/applicant/login',
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
