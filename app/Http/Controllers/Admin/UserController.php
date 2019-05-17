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
        $users = User::select(
            'id',
            'name',
            'email',
            'nicename',
            'avatar',
            'timezone',
            'registered_at',
            'is_admin',
            'status'
        )
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $users;
    }

    public function update(Request $request)
    {
        $id   = $request->post('id');
        $user = User::findOrFail($id);
        $data = $request->only(['nicename', 'email', 'name', 'password', 'status']);
        $user->fill($data);
        $user->save();
        return response([], 200);
    }

    public function add(Request $request)
    {
        $data = $request->only(['nicename', 'email', 'name', 'password', 'status']);
        $user = new User($data);
        $user->save();
        return response([], 200);
    }
}
