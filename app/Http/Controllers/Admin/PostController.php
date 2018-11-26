<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use App\Traits\NullToString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    private $fields = [
        'post_author'           => '',
        'post_content'          => '',
        'post_title'            => '',
        'post_excerpt'          => '',
        'post_status'           => '',
        'comment_status'        => '',
        'ping_status'           => '',
        'post_password'         => '',
        'post_name'             => '',
        'to_ping'               => '',
        'pinged'                => '',
        'post_date'             => '',
        'post_date_gmt'         => '',
        'post_modified'         => '',
        'post_modified_gmt'     => '',
        'post_content_filtered' => '',
        'post_parent'           => '',
        'guid'                  => '',
        'menu_order'            => '',
        'post_type'             => '',
        'post_mime_type'        => '',
    ];

    //
    public function show($id)
    {
        $post = Post::where('id', $id)
            ->first();
        if ($post) {
            unset($post->author->user_pass);
            unset($post->post_password);
        }
        return $post;
    }

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $posts = Post::orderBy('id', 'desc')->paginate($limit);
        foreach ($posts as $post) {
            $post->access_api = url("/api/post/{$post->post_name}/edit");
            unset($post->author->user_pass);
            unset($post->post_password);
        }
        return $posts;
    }

    public function add(Request $request)
    {
        $post   = new Post();
        $fields = $this->fields;
        foreach ($fields as $fields => $deal) {
            if (!isset($request->$fields)) {
                continue;
            }
            if (empty($deal)) {
                $post->$fields = $request->$fields;
            }
        }
        $post->save();
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
        $fields = $this->fields;
        foreach ($fields as $fields => $deal) {
            if (!isset($request->$fields)) {
                continue;
            }
            if (empty($deal)) {
                $post->$fields = $request->$fields;
            }
        }
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
        $file    = $request->file('file');
        $oriName = $file->getClientOriginalName();
        $path    = $file->store('private/torrent');
        return response([
            'msg'  => 'hello upload torrent',
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
