<?php

namespace App\Notifications;

use App\Mail\AdminResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);
        $expireMinutes = config('auth.passwords.users.expire', 60);
        $userName = $notifiable->full_name ?? $notifiable->name ?? 'User';
        $email = $notifiable->getEmailForPasswordReset();

        // Create Mailable with recipient email
        return new AdminResetPasswordMail($url, $userName, $expireMinutes, $email);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        // Generate URL with token in path and email as query parameter
        $url = route('admin.password.reset', ['token' => $this->token], false);
        $url .= '?email=' . urlencode($notifiable->getEmailForPasswordReset());
        return url($url);
    }
}

