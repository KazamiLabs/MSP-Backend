<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin           = new User();
        $admin->name     = 'tsukasa';
        $admin->email    = 'tsukasa.kzk@gmail.com';
        $admin->password = Hash::make('secret');
        $admin->nicename = 'Tsukasa Kanzaki';
        $admin->avatar   = 'https://avatars0.githubusercontent.com/u/13465532?s=460&v=4';
        $admin->timezone = 'Asia/Shanghai';
        $admin->is_admin = 1;
        $admin->status   = 1;
        $admin->save();
        $users = factory(User::class, 4);
        $users->create();
    }
}
