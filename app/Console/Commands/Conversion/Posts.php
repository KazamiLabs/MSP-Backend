<?php

namespace App\Console\Commands\Conversion;

use App\Bangumi;
use App\Post;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        config([
            'database.connections' => array_merge(
                config('database.connections'),
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
                    $post   = new Post();
                    $author = User::find($postData->post_author);
                    $post->fill((array) $postData);
                    $post->cover = $cover;
                    $post->author()->associate($author);
                    $post->save();
                    if ($postData->filename) {
                        $bangumi = new Bangumi([
                            'filename'   => $postData->filename,
                            'filepath'   => $postData->filepath,
                            'group_name' => $postData->author,
                            'title'      => $postData->title,
                            'year'       => $postData->year,
                        ]);
                        $post->bangumi()->save($bangumi);
                    }
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
