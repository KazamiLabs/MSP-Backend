<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //

    public function show($postName)
    {
        $post = Post::where('post_name', $postName)
            ->findOrFail();
        return $post;
    }

    public function getList()
    {
        $posts = Post::where('post_status', 'publish')
            ->select('id', 'post_author', 'post_title', 'post_excerpt', 'post_name', 'post_date', 'post_date_gmt')
            ->paginate(15);
        foreach ($posts as $post) {
            $post->access_api = url("/api/post/{$post->post_name}");
        }
        return $posts;
    }

    public function torrentDownload($id)
    {
        $post = Post::findOrFail($id);
        return response()->download($post->bangumi->filepath, $post->bangumi->encode_filename);
    }
}
