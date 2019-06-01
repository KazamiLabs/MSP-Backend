<?php

namespace App\Console\Commands\Conversion;

use App\Bangumi;
use App\BangumiTransferLog;
use App\Post;
use App\User;
use Converter\BBCodeConverter;
use Converter\HTMLConverter;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Posts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert posts data from wordpress system.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // 载入新数据库链接
        Config::set([
            'database.connections' => array_merge(
                Config::get('database.connections'),
                [
                    'wpmysql' => [
                        'driver'      => 'mysql',
                        'host'        => env('WP_DB_HOST', '127.0.0.1'),
                        'port'        => env('WP_DB_PORT', '3306'),
                        'database'    => env('WP_DB_DATABASE', 'forge'),
                        'username'    => env('WP_DB_USERNAME', 'forge'),
                        'password'    => env('WP_DB_PASSWORD', ''),
                        'unix_socket' => env('WP_DB_SOCKET', ''),
                        'charset'     => 'utf8mb4',
                        'collation'   => 'utf8mb4_unicode_ci',
                        'prefix'      => '',
                        'strict'      => true,
                        'engine'      => null,
                    ],
                ]
            ),
        ]);

        // 载入旧程序路径
        Config::set('mabors.wordpress.root_path', env('WP_ROOT_PATH'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $chunk     = 5;
        $postCount = DB::connection('wpmysql')
            ->table('ms_posts')
            ->count();
        // $step = \ceil($postCount / $chunk);
        $step = $postCount;
        $bar  = $this->output->createProgressBar($step);
        $bar->start();
        // DB::statement('TRUNCATE `posts`;');
        // DB::statement('TRUNCATE `bangumis`;');
        DB::statement('SET sql_mode=\'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\'');
        DB::connection('wpmysql')
            ->table('ms_posts')
            ->leftJoin('ms_posts_bangumi', 'ms_posts_bangumi.post_id', '=', 'ms_posts.ID')
            ->select(
                'ms_posts.*',
                'ms_posts_bangumi.filename',
                'ms_posts_bangumi.filepath',
                'ms_posts_bangumi.author',
                'ms_posts_bangumi.title',
                'ms_posts_bangumi.year'
            )
            ->orderBy('ID')
            ->chunk($chunk, function ($posts) use (&$bar) {
                $posts->each(function ($postData) use (&$bar) {
                    $matches = [];

                    if (preg_match(
                        '/<img [^>]*src=("[^"]*"|\'[^\']*\')/i',
                        $postData->post_content,
                        $matches
                    )) {
                        $cover = $matches[1];
                    } else {
                        $cover = '';
                    }

                    // Markdown 转换
                    $html_coverter           = new HTMLConverter($postData->post_content);
                    $bbcode_coverter         = new BBCodeConverter($html_coverter->toBBCode());
                    $postData->markdown_code = $bbcode_coverter->toMarkdown();

                    // 已完成状态转换
                    if ($postData->post_status === 'publish') {
                        $postData->post_status = 'published';
                    }

                    $post   = new Post();
                    $author = User::find($postData->post_author);
                    $post->fill((array) $postData);
                    $post->cover = $cover;
                    $post->author()->associate($author);
                    $post->save();
                    if ($postData->filename) {
                        $bangumi = new Bangumi([
                            'filename'   => $postData->filename,
                            'filepath'   => Str::replaceFirst('/mbrs-torrent', 'private/torrent', $postData->filepath),
                            'group_name' => $postData->author,
                            'title'      => $postData->title,
                            'year'       => $postData->year,
                        ]);

                        Storage::putFileAs(
                            'private/torrent',
                            new File(Config::get('mabors.wordpress.root_path') . "/wp-content{$postData->filepath}"),
                            pathinfo($postData->filepath, PATHINFO_BASENAME)
                        );
                        $post->bangumi()->save($bangumi);
                    }

                    // 传输日志查询
                    $transferLogs = DB::connection('wpmysql')
                        ->table('ms_posts_bangumi_transferlog')
                        ->where('post_id', $postData->ID)
                        ->get()
                        ->each(function ($logData) use ($post) {
                            $log = new BangumiTransferLog();
                            $log->fill((array) $logData);
                            $log->sitedriver = Str::replaceFirst('kl\\\\', '', $log->sitedriver);
                            // 日志文件名拆分与重新组装
                            $logFilePath = Str::after($logData->log_file, 'mbrs-cron');
                            $logFilePath = Config::get('mabors.wordpress.root_path') . "/mbrs-cron{$logFilePath}";
                            $log->log    = file_get_contents($logFilePath);
                            $post->bangumiTransferLogs()->save($log);
                        });

                    $post = null;
                    $bar->advance();
                });
            });
        $bar->finish();
        $this->line('');
        $this->info('Post conversation has done!');
        return 0;
    }
}
