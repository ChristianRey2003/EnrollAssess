<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('BSIT Entrance Exam Notification')
                    ->view('emails.exam-notification');
    }
}

