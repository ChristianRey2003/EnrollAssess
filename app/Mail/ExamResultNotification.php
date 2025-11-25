<?php

namespace App\Mail;

use App\Models\Applicant;
use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamResultNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;
    public $result;
    public $passed;
    public $score;
    public $totalQuestions;
    public $percentage;
    public $passingScore;

    /**
     * Create a new message instance.
     */
    public function __construct(Applicant $applicant, Result $result, $passingScore = 70)
    {
        $this->applicant = $applicant;
        $this->result = $result;
        $this->score = $result->score;
        $this->totalQuestions = $result->total_questions;
        $this->percentage = $this->totalQuestions > 0 
            ? round(($this->score / $this->totalQuestions) * 100, 2) 
            : 0;
        $this->passingScore = $passingScore;
        $this->passed = $this->percentage >= $passingScore;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->passed 
            ? 'Congratulations! Exam Results - PASSED' 
            : 'Exam Results - Review Required';

        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', 'EnrollAssess System');
        
        // Ensure we have a from address
        if (empty($fromAddress)) {
            $fromAddress = 'noreply@evsu.edu.ph';
        }

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
            view: 'emails.exam-result',
            text: 'emails.exam-result-text',
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
