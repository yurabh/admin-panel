<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class RegistrationConfirmMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user)
    {
    }

    public function content(): Content
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        return new Content(
            htmlString: "
            <h1>Вітаємо, {$this->user->name}!</h1>
            <p>Дякуємо за реєстрацію. Щоб завершити, натисніть кнопку нижче:</p>
            <p>
                <a href='{$url}' style='background: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                    Підтвердити пошту
                </a>
            </p>
        ",
        );
    }
}
