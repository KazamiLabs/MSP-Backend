<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    //

    public function index()
    {
        $posts = Post::paginate(10);
        return view('portal')->with('posts', $posts);
    }
}
