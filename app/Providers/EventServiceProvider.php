<?php

namespace App\Providers;

use App\Events\PostCreating;
use App\Events\PostUpdating;
use App\Events\UserCreating;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use App\Listeners\CheckAndSetPostGuid;
use App\Listeners\CheckAndSetPostExcerpt;
use App\Listeners\SetUserRegisteredDateTime;
use App\Listeners\SetPostDateTimeForCreating;
use App\Listeners\SetPostDateTimeForUpdating;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        PostCreating::class => [
            SetPostDateTimeForCreating::class,
            CheckAndSetPostExcerpt::class,
            CheckAndSetPostGuid::class,
        ],
        PostUpdating::class => [
            SetPostDateTimeForUpdating::class,
            CheckAndSetPostExcerpt::class,
            CheckAndSetPostGuid::class,
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
