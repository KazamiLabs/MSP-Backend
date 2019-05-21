<?php

namespace App\Console\Commands;

use App\Bangumi;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class FileExpireListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for file uploads.
    If it is not stored in the database, delete the file after the cache record expires.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $dbindex = Config::get('database.redis.cache.database', 0);
        $prefix  = Cache::store('redis')->getPrefix();
        $this->info("Cache prefix: {$prefix}");
        $pattern = '__keyevent@' . $dbindex . '__:expired';
        Redis::subscribe([$pattern], function ($channel) use ($prefix) {
            // 订阅键过期事件
            $s_channel = Str::replaceFirst($prefix, '', $channel);
            $key_type  = Str::before($s_channel, ':');
            $this->info("Key Type:{$key_type}, Channel {$channel}");
            switch ($key_type) {
                case 'TORRENT_CONFIRM':
                    $filename = Str::after($s_channel, ':');

                    // 获取完整路径
                    $path = Bangumi::getTorrentFullPath($filename);

                    // 删除
                    $this->info("Delete the torrent file: {$path}");
                    Storage::delete($path);
                    $this->info("Torrent file: {$path} has been deleted.");
                    break;
                // case 'TORRENT_OTHEREVENT':
                //     break;
                default:
                    break;
            }
        });
    }
}
