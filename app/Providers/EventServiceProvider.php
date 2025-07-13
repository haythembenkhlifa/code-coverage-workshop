<?php

namespace App\Providers;

use App\Events\PostCreated;
use App\Events\PostUpdated;
use App\Listeners\HandlePostCreated;
use App\Listeners\HandlePostUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PostCreated::class => [
            HandlePostCreated::class,
        ],
        PostUpdated::class => [
            HandlePostUpdated::class,
        ],
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
