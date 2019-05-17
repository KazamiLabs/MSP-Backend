<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

class SetUserRegisteredDateTime
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserCreating  $event
     * @return void
     */
    public function handle(UserCreating $event)
    {
        //
        $event->user->registered_at = Carbon::now('UTC')->toDateTimeString();
    }
}
