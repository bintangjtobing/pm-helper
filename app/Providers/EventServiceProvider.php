<?php

namespace App\Providers;

use App\Events\DatabaseNotificationsSent;
use App\Listeners\SocialRegistration;
use DutchCodingCompany\FilamentSocialite\Events\Registered as SocialRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

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
        SocialRegistered::class => [
            SocialRegistration::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Broadcast when a database notification is sent
        \Illuminate\Support\Facades\Event::listen(NotificationSent::class, function (NotificationSent $event) {
            if ($event->channel === 'database') {
                broadcast(new DatabaseNotificationsSent($event->notifiable->id));
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
