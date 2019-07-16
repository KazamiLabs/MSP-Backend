<?php

namespace App\Events;

use App\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SyncQueuesChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $has_done = true;

    public $length = 0;

    public $list;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $keys = Collection::make(
            Cache::store('redis')
                ->connection()
                ->keys(Cache::store('redis')->getPrefix() . Post::getQueueListKey())
        )
            ->map(function ($key) {
                return Str::replaceFirst(Cache::store('redis')->getPrefix(), '', $key);
            });

        if ($keys->isNotEmpty()) {
            $list = Collection::make(Cache::store('redis')->many($keys->all()));
        } else {
            $list = Collection::make();
        }

        $processingStatus = Collection::make([
            'pending', 'processing',
        ]);

        $this->list = $list->each(function ($item) use ($processingStatus) {
            if ($processingStatus->contains($item['status'])) {
                $this->has_done = false;
                ++$this->length;
            }
        })
            ->values();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('Queues');
    }
}
