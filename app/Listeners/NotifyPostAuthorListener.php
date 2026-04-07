<?php

namespace App\Listeners;

use App\Events\NewCommentEvent;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class NotifyPostAuthorListener implements ShouldQueue
{
    public function handle(NewCommentEvent $event): void
    {
        $comment = $event->comment;
        $author = $comment?->post?->user;

        if (!$author) {
            Log::warning("Could not notify author: post or user no longer exists for comment ID: " . ($comment->id ?? 'unknown'));
            return;
        }
        if ($author->id !== $event->comment->user_id) {
            $author->notify(new NewCommentNotification($event->comment));
        }
    }
}
