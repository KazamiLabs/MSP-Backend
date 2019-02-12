<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    private $users;
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function hello()
    {
        var_dump($this->users);
    }

    public function search(Request $request)
    {
        $limit = $request->get('limit', 5);

        if ($request->has('name')) {
            $search_user = $request->get('name');
            return User::searchSelect()->searchCondition($search_user)->paginate($limit);
        } else {
            return User::searchSelect()->paginate($limit);
        }

    }
}
