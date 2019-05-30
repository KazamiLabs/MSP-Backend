<?php

namespace App\Http\Controllers\Admin;

use App\Bangumi;
use App\BangumiSetting;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPublishList;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
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
            ->whereNotIn('post_status', ['inherit', 'auto-draft'])
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $posts;
    }

    public function add(Request $request)
    {
        $post   = new Post($request->all());
        $author = auth('api')->user();
        $post->author()->associate($author);
        $post->save();
        $bangumi = new Bangumi($request->bangumi);
        $post->bangumi()->save($bangumi);
        // 队列分发
        BangumiSetting::where('status', 1)
            ->get()
            ->each(function ($setting) use ($post) {
                ProcessPublishList::dispatch($post, $setting);
            });
        // 删除缓存中的种子文件记录
        Cache::store('redis')
            ->forget('TORRENT_CONFIRM:' . pathinfo($bangumi->filepath, PATHINFO_BASENAME));
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
        // 删除缓存中的种子文件记录
        Cache::store('redis')
            ->forget('TORRENT_CONFIRM:' . pathinfo($bangumi->filepath, PATHINFO_BASENAME));
        return response(null, 200);
    }

    public function uploadPic(Request $request)
    {
        $path = $request->file('file')->store('public/posts');
        return [
            'msg'    => 'Picture has been uploaded.',
            'path'   => $path,
            'assert' => Config::get('app.url') . Storage::url($path),
        ];
    }

    public function uploadTorrent(Request $request)
    {
        $file    = $request->file('file');
        $oriName = $file->getClientOriginalName();
        // 保存文件名构建
        $s_name     = \torrent_hash($file->getRealPath());
        $ext        = pathinfo($oriName, PATHINFO_EXTENSION);
        $s_fullname = "{$s_name}.{$ext}";
        // 标题信息，字幕组信息提取
        $title     = self::pregTitle($oriName);
        $groupName = self::pregGroupName($oriName);
        // 保存
        $path = $file->storeAs(Bangumi::TORRENT_PATH, $s_fullname);
        // 设置保存的文件名 30 分钟后过期，以触发过期删除事件
        Cache::store('redis')
            ->put('TORRENT_CONFIRM:' . $s_fullname, $path, 1800);
        return [
            'msg'        => 'Torrent has been uploaded',
            'filepath'   => $path,
            'filename'   => $oriName,
            'title'      => $title,
            'group_name' => $groupName,
        ];
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
        $keys = new Collection(Redis::keys(Post::getQueueListKey()));
        $list = new Collection();
        $keys->each(function ($key) use ($list) {
            $value = Redis::get($key);
            $value = json_decode($value, true);
            if ($value) {
                $list->push($value);
            }
        });
        return response($list, $list->isNotEmpty() ? 200 : 204);
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
