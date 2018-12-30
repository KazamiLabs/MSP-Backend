<?php

namespace App\Http\Controllers\Admin;

use App\Bangumi;
use App\Http\Controllers\Controller;
use App\Post;
use App\Traits\NullToString;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //
    public function show($id)
    {
        $post = Post::findOrFail($id);
        if ($post) {
            unset($post->author->user_pass);
            unset($post->post_password);
            $post->bangumi;
        }
        return $post;
    }

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $posts = Post::where('post_status', '!=', 'inherit')->orderBy('id', 'desc')->paginate($limit);
        foreach ($posts as $post) {
            $post->access_api = url("/api/post/{$post->post_name}/edit");
            unset($post->author->user_pass);
            unset($post->post_password);
        }
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
        // $fields = $post->getFillable();
        // foreach ($fields as $field) {
        //     if (!isset($request->$field)) {
        //         continue;
        //     }
        //     if (empty($deal)) {
        //         $post->$field = $request->$field;
        //     }
        // }
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
        $s_name    = hash_file('SHA1', $file->getRealPath());
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
