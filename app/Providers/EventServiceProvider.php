<?php

namespace App\Providers;

use App\Events\NewCommentEvent;
use App\Events\RegistrationEvent;
use App\Listeners\NotifyPostAuthorListener;
use App\Listeners\RegistrationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;

class EventServiceProvider extends BaseEventServiceProvider
{

    protected $listen = [
        RegistrationEvent::class => [
            RegistrationListener::class,
        ],
        NewCommentEvent::class => [
            NotifyPostAuthorListener::class,
        ],
    ];



    /**
     * Register services.
     */
    public function register(): void
    {
        BaseEventServiceProvider::disableEventDiscovery();

        parent::register();
    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
