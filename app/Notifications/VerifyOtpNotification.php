<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class VerifyOtpNotification extends Notification
{
    public function __construct(public string $code) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Registration Confirmation')
            ->greeting('Thank you for registering with ' . config('app.name') . '!')
            ->line(new HtmlString('Your verification code is <strong>' . e($this->code) . '</strong>.'))
            ->line('This code expires in 10 minutes.')
            ->line('If you did not create an account, you can ignore this email.');
    }
}
