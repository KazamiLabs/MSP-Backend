<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function find(int $uid)
    {
        return DB::table('users')
            ->where('uid', '=', $uid)
            ->select(
                'uid',
                'email',
                'username'
            )
            ->get();
    }
}