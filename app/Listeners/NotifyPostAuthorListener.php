<?php

namespace App\Listeners;

use App\Events\NewCommentEvent;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyPostAuthorListener implements ShouldQueue
{
    public function handle(NewCommentEvent $event): void
    {
        $author = $event->comment->post->user;
        if ($author->id !== $event->comment->user_id) {
            $author->notify(new NewCommentNotification($event->comment));
        }
    }
}
