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
}
