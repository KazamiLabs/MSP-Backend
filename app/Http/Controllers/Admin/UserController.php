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

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $users = User::select('id', 'name', 'email', 'nicename', 'avatar', 'timezone', 'registered', 'is_admin', 'status')
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $users;
    }
}
