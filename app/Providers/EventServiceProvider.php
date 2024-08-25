<?php

namespace App\Providers;

use App\Events\PasswordExpiredEvent;
use App\Events\UserCreated;
use App\Events\WelcomeMailEvent;
use App\Listeners\PasswordExpiredListener;
use App\Listeners\SendAccountSuccessMail;
use App\Listeners\SendWelcomeCreatedMail;
use App\Listeners\SendWelcomeMailListener;
use App\Listeners\TwoFactorAuthenticationConfirmedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserCreated::class => [
            SendWelcomeMailListener::class
        ],
        PasswordExpiredEvent::class => [
            PasswordExpiredListener::class,
        ]
       
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
