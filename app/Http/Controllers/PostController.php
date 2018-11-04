<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //

    public function show($id)
    {
        $post = Post::select('id', 'post_author', 'post_content', 'post_title', 'post_status',
            'comment_status', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'post_type', 'post_mime_type', 'comment_count')
            ->where('post_name', $id)
            ->whereOr('id', $id)
            ->first();
        return $post;
    }

    public function getList(Request $request)
    {
        $posts = Post::where('post_status', 'publish')
            ->select('id', 'post_author', 'post_title', 'post_excerpt', 'post_name', 'post_date', 'post_date_gmt')
            ->paginate(15);
        foreach ($posts as $post) {
            $post->access_api = url("/api/post/{$post->post_name}");
        }
        return $posts;
    }

    public function add(Request $request)
    {
        $post                        = new Post();
        $post->post_author           = $request->post_author;
        $post->post_content          = $request->post_content;
        $post->post_title            = $request->post_title;
        $post->post_excerpt          = $request->post_excerpt;
        $post->post_status           = $request->post_status;
        $post->comment_status        = $request->comment_status;
        $post->ping_status           = $request->ping_status;
        $post->post_password         = $request->post_password;
        $post->post_name             = $request->post_name;
        $post->to_ping               = $request->to_ping;
        $post->pinged                = $request->pinged;
        $post->post_date             = $request->post_date;
        $post->post_date_gmt         = $request->post_date_gmt;
        $post->post_modified         = $request->post_modified;
        $post->post_modified_gmt     = $request->post_modified_gmt;
        $post->post_content_filtered = $request->post_content_filtered;
        $post->post_parent           = $request->post_parent;
        $post->guid                  = $request->guid;
        $post->menu_order            = $request->menu_order;
        $post->post_type             = $request->post_type;
        $post->post_mime_type        = $request->post_mime_type;
        $post->comment_count         = $request->comment_count;

    }
}
