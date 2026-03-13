<?php

namespace App\Providers;

use App\Events\RegistrationEvent;
use App\Listeners\RegistrationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;

class EventServiceProvider extends BaseEventServiceProvider
{

    protected $listen = [
        RegistrationEvent::class => [
            RegistrationListener::class,
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
