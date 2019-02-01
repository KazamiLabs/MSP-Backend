<?php

namespace App\Http\Controllers\Admin;

use App\Bangumi;
use App\BangumiSetting;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPublishList;
use App\Post;
use App\Traits\NullToString;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    const SHOW_QUEUE_KEY = 'posts_sync:queues';
    //
    public function show($id)
    {
        $post = Post::with(['author:id,nicename', 'bangumi'])->findOrFail($id);
        return $post;
    }

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $posts = Post::with(['author:id,nicename'])
            ->select(
                'id', 'post_author', 'post_title',
                'post_excerpt', 'post_status', 'comment_status',
                'post_name', 'post_date', 'post_date_gmt',
                'post_modified', 'post_modified_gmt', 'post_content_filtered',
                'post_type', 'post_mime_type', 'comment_count', 'created_at',
                'updated_at')
            ->where('post_status', '!=', 'inherit')
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $posts;
    }

    public function add(Request $request)
    {
        $post   = new Post($request->toArray());
        $author = auth('api')->user();
        $post->author()->associate($author);
        $post->save();
        $bangumi = new Bangumi($request->bangumi);
        $post->bangumi()->save($bangumi);
        // 队列分发
        $settings = BangumiSetting::where('status', 1)->get();
        foreach ($settings as $setting) {
            ProcessPublishList::dispatch($post, $setting);
            // 使用 Redis 存储发布队列
            Redis::rpush(self::SHOW_QUEUE_KEY, json_encode([
                'post_title' => $post->post_title,
                'sitename'   => $setting->sitename,
                'status'     => 'pending',
            ]));
        }
        return response(null, 201);
    }

    public function update(Request $request, int $id)
    {
        if (empty($id)) {
            return response(['msg' => 'ID非法'], 400);
        }
        $post = Post::find($id);
        if (empty($post)) {
            return response(['msg' => '找不到对应的文章'], 400);
        }
        $post->fill($request->toArray());
        $bangumi = $post->bangumi->fill($request->bangumi);
        $bangumi->save();
        $post->save();
        return response(null, 200);
    }

    public function uploadPic(Request $request)
    {
        $path = $request->file('file')->store('public/posts');
        return response([
            'msg'    => 'hello uploadPic',
            'path'   => $path,
            'assert' => Config::get('app.url') . Storage::url($path),
        ], 200);
    }

    public function uploadTorrent(Request $request)
    {
        $file      = $request->file('file');
        $s_name    = \torrent_hash($file->getRealPath());
        $oriName   = $file->getClientOriginalName();
        $ext       = pathinfo($oriName, PATHINFO_EXTENSION);
        $title     = self::pregTitle($oriName);
        $groupName = self::pregGroupName($oriName);
        $path      = $file->storeAs('private/torrent', "{$s_name}.{$ext}");
        return response([
            'msg'        => 'hello upload torrent',
            'filepath'   => $path,
            'filename'   => $file->getClientOriginalName(),
            'title'      => $title,
            'group_name' => $groupName,
        ]);
    }

    public function changeStatus(Request $request, int $id)
    {
        $post   = Post::findOrFail($id);
        $status = $request->post('status');

        $post->post_status = $status;
        $post->save();
        return response([
            'status' => $status,
            'id'     => $id,
        ]);
    }

    public function deletePost(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response([], 204);
    }

    public function queues(Request $request)
    {
        $queues = Redis::lrange(self::SHOW_QUEUE_KEY, 0, 15);
        $queues = array_map(function ($queue) {
            return json_decode($queue);
        }, $queues);
        return response($queues, count($queues) > 0 ? 200 : 204);
    }

    /**
     * 标题匹配
     *
     * @param string $filename
     * @return void
     * Kanzaki Tsukasa
     */
    private static function pregTitle(string $filename)
    {
        $regx    = '/(?<=】).+?(?=\[)|(?<=\]).+?(?=\(|\[)/u';
        $matches = [];
        if (preg_match($regx, $filename, $matches)) {
            return trim($matches[0]);
        } else {
            return '';
        }
    }

    /**
     * 发布身份匹配
     *
     * @param string $filename
     * @return void
     * Kanzaki Tsukasa
     */
    private static function pregGroupName(string $filename)
    {
        $regx    = '/(?<=\[)[\w|-]+(?=\])|(?<=【)\w+(?=】)/u';
        $matches = [];
        if (preg_match($regx, $filename, $matches)) {
            return trim($matches[0]);
        } else {
            return '';
        }
    }
}
