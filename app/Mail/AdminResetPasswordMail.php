<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $userName;
    public $expireMinutes;
    public $toEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($resetUrl, $userName, $expireMinutes = 60, $toEmail = null)
    {
        $this->resetUrl = $resetUrl;
        $this->userName = $userName;
        $this->expireMinutes = $expireMinutes;
        $this->toEmail = $toEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: 'Reset Password Notification - Faculty Portal',
        );

        // Set recipient if provided (Laravel notifications should set this automatically,
        // but we set it here as a fallback)
        if ($this->toEmail) {
            $envelope->to(new Address($this->toEmail));
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-reset-password',
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

