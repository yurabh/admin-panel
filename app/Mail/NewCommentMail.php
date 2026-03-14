<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * @property $comment
 */
class NewCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Comment Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comment',
            with: [
                'comment' => $this->comment,
            ],
        );
    }
}
