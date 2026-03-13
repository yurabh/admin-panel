<?php

namespace App\Listeners;

use App\Events\RegistrationEvent;
use App\Notifications\RegistrationEmailConfirmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationListener implements ShouldQueue
{
    public function handle(RegistrationEvent $event): void
    {
        $event->user->notify(new RegistrationEmailConfirmNotification());
    }
}
