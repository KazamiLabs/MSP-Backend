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
        $tks           = new User();
        $tks->name     = 'tsukasa';
        $tks->email    = 'tsukasa.kzk@gmail.com';
        $tks->password = Hash::make('secret');
        $tks->nicename = 'Tsukasa Kanzaki';
        $tks->avatar   = 'https://avatars0.githubusercontent.com/u/13465532?s=460&v=4';
        $tks->timezone = 'Asia/Shanghai';
        $tks->is_admin = 1;
        $tks->status   = 1;
        $tks->save();

        $miemie           = new User();
        $miemie->name     = 'Eustia';
        $miemie->email    = '767471286@qq.com';
        $miemie->password = Hash::make('secret');
        $miemie->nicename = '尤斯蒂娅';
        $miemie->avatar   = 'https://avatars2.githubusercontent.com/u/5292387?s=460&v=4';
        $miemie->timezone = 'Asia/Shanghai';
        $miemie->is_admin = 1;
        $miemie->status   = 1;
        $miemie->save();
        $users = factory(User::class, 4);
        $users->create();
    }
}
