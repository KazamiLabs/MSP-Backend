<?php

namespace App\Jobs;

use App\BangumiSetting;
use App\Events\SyncQueuesChange;
use App\Post;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPublishList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PENDING_EXPIRE    = 3600;
    const PROCESSING_EXIPRE = 3600;
    const SUCCESS_EXPIRE    = 3600;
    const FAILED_EXPIRE     = 86400;

    protected $post;
    protected $setting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post, BangumiSetting $setting)
    {
        //
        $this->dispatchChangeEvent();
        $this->post    = $post;
        $this->setting = $setting;
        Cache::store('redis')->set($post->getQueueKey($setting), [
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'pending',
        ], self::PENDING_EXPIRE);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $post    = $this->post;
        $setting = $this->setting;

        // 标记队列为处理中
        Cache::store('redis')->set($post->getQueueKey($setting), [
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'processing',
        ], self::PENDING_EXPIRE);

        // 开始处理
        $class = "\\App\\Drivers\\Bangumi\\{$setting->sitedriver}";

        $driver = App::make($class, [
            'username' => $setting->username,
            'password' => $setting->password,
        ]);

        $driver->post_id      = $post->id;
        $driver->author       = $post->post_author;
        $driver->title        = $post->post_title;
        $driver->content      = $post->post_content;
        $driver->year         = $post->bangumi->year;
        $driver->bangumi      = $post->bangumi->title;
        $driver->torrent_name = $post->bangumi->filename;
        $driver->torrent_path = Storage::path($post->bangumi->filepath);
        $driver->upload();
        $driver->callback();

        // 标记队列为已完成
        Cache::store('redis')->set($post->getQueueKey($setting), [
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'finished',
        ], self::SUCCESS_EXPIRE);
    }

    /**
     * 任务失败的处理过程
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // 任务失败时标记失败
        $post    = $this->post;
        $setting = $this->setting;
        Log::error("Queue Failed: {$exception->getMessage()}", [$post, $setting]);
        Cache::store('redis')->set($post->getQueueKey($setting), [
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'failed',
            'post_id'    => $post->id,
            'setting_id' => $setting->id,
        ], self::FAILED_EXPIRE);
    }

    public function __destruct()
    {
        $this->dispatchChangeEvent();
    }

    /**
     * 分发队列变动事件
     *
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-07-16
     */
    private function dispatchChangeEvent()
    {
        App::make('events')->dispatch(new SyncQueuesChange);
    }
}
