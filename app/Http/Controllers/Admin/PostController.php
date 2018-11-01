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
        unset($post->author->user_pass);
        unset($post->post_password);
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
}
