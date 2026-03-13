<?php

namespace App\Notifications;

use App\Mail\RegistrationConfirmMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class RegistrationEmailConfirmNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }


    public function toMail(object $notifiable): Mailable
    {
        return (new RegistrationConfirmMail($notifiable))
            ->to($notifiable->email);
    }
}
