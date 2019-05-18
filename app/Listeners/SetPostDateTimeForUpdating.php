<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

class SetPostDateTimeForUpdating
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
        $event->post->post_modified     = Carbon::now()->toDateTimeString();
        $event->post->post_modified_gmt = Carbon::now('UTC')->toDateTimeString();
    }
}
