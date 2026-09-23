<?php

namespace App\Notifications;

use App\Models\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class VerifyOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        $settings = GeneralSetting::first()->only(['site_name', 'site_email', 'site_logo']);

        return (new MailMessage)
            ->subject('Registration Confirmation')
            ->from($settings['site_email'], $settings['site_name'])
            ->greeting('Thank you for registering with '.$settings['site_name'].'!')
            ->line(new HtmlString('Your verification code is <strong>'.e($this->code).'</strong>.'))
            ->line('This code expires in 10 minutes.')
            ->line('If you did not create an account, you can ignore this email.');
    }
}
