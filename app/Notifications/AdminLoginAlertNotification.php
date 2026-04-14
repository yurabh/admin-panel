<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public string $adminName) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('System notification: Login Admin')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line("Admin **{$this->adminName}** now logged into admin panel management")
            ->line('Date login: '.now()->format('H:i:s d.m.Y'))
            ->action('', url('/'));
    }
}
