<?php

namespace App\Providers;

use App\Events\UserCreating;
use App\Listeners\SetUserRegisteredDateTime;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 'App\Events\Event'  => [
        //     'App\Listeners\EventListener',
        // ],
        UserCreating::class => [
            SetUserRegisteredDateTime::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
        Redis::enableEvents();
    }
}
