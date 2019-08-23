<?php

namespace App\Events;

use App\BangumiSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckSyncAccountFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Bangumi Account Setting
     *
     * @var BangumiSetting
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-08-22
     */
    public $bangumiSetting;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BangumiSetting $bangumiSetting)
    {
        //
        $this->bangumiSetting = $bangumiSetting;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('dashboard');
    }
}
