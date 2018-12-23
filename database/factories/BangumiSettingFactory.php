<?php

use Faker\Generator as Faker;
use Illuminate\Contracts\Encryption\Encrypter;

$factory->define(App\BangumiSetting::class, function (Faker $faker) {
    $sites = [
        ['ACG.RIP', 'AcgRip'],
        ['萌番组', 'MoeBangumi'],
        ['动漫花园', 'Dmhy'],
        ['Nyaa', 'Nyaa'],
        ['爱恋动漫BT下载', 'Kisssub'],
    ];
    $passwordList = ['secret', 'woaiyeqi', 'bakamie', 'maborsSaiko'];
    $username     = $faker->userName;
    $password     = $passwordList[rand(0, count($passwordList) - 1)];
    $siteIndex    = rand(0, count($sites) - 1);
    $site         = $sites[$siteIndex];
    return [
        //
        'sitename'   => $site[0],
        'sitedriver' => $site[1],
        'username'   => $username,
        'password'   => $password,
        'status'     => true,
    ];
});
