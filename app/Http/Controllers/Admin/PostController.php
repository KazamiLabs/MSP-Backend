<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
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
        $posts = Post::paginate($limit);
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
        $fields = [
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
            'comment_count'         => '',
        ];
        foreach ($fields as $fields => $deal) {
            if (empty($deal)) {
                $post->$fields = $request->$fields;
            }
        }
        $post->save();
    }
}
