<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    public function search(Request $request)
    {
        $userM = new User();

        if ($request->has('name')) {
            $search_user = $request->get('name');
            $userM->where('user_login', $search_user)
                ->whereOr('user_nicename', $search_user)
                ->whereOr('user_email', $search_user);
        }
        return $userM->select('id', 'user_nicename')->get();
    }
}
