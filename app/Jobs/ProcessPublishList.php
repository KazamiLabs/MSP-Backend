<?php

namespace App\Jobs;

use App\Post;
use Exception;
use App\BangumiSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessPublishList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->post    = $post;
        $this->setting = $setting;
        Redis::set($post->getQueueKey($setting), json_encode([
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'pending',
        ]));
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
        Redis::set($post->getQueueKey($setting), json_encode([
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'processing',
        ]));

        // 开始处理
        $class = "\\App\\Drivers\\Bangumi\\{$setting->sitedriver}";
        if (!class_exists($class)) {
            throw new \Exception("Class not exists! {$class}");
        }
        $driver = new $class(
            $setting->username,
            $setting->password,
            ''
        );
        $driver->post_id      = $post->id;
        $driver->author       = $post->post_author;
        $driver->title        = $post->post_title;
        $driver->content      = $post->post_content;
        $driver->bangumi      = $post->bangumi->title;
        $driver->torrent_name = $post->bangumi->filename;
        $driver->torrent_path = storage_path("app/{$post->bangumi->filepath}");
        $driver->login();
        $driver->upload();
        $driver->callback();

        // $time = rand(30, 60);
        // sleep($time);

        // 标记队列为已完成
        Redis::set($post->getQueueKey($setting), json_encode([
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'finished',
        ]), 'EX', '86400');
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
        Log::info('Info', [$post, $setting]);
        Redis::set($post->getQueueKey($setting), json_encode([
            'post_title' => $post->post_title,
            'sitename'   => $setting->sitename,
            'status'     => 'failed',
        ]));
    }
}
