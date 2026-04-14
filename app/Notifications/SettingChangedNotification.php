<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SettingChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public string $settingKey) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Change setting options')
            ->line("message, parameters **{$this->settingKey}** were changed.")
            ->line('Please update site.')
            ->action('See site', url('/'));
    }
}
