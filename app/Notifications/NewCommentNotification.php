<?php

namespace App\Notifications;

use App\Mail\NewCommentMail;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment)
    {
    }


    public function via(object $notifiable): array
    {
        return ['mail'];
    }


    public function toMail(object $notifiable): NewCommentMail
    {
        return (new NewCommentMail($this->comment))
            ->to($notifiable->email);
    }
}
