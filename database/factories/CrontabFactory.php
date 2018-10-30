<?php

use Faker\Factory;
use Faker\Generator as Faker;

$factory->define(App\Crontab::class, function (Faker $faker) {
    $time   = time() + rand(60, 150);
    $n_time = $time + rand(0, 59);
    return [
        'todo'             => $faker->text,
        'user_id'          => '1298426440',
        'src_group_id'     => '857053153',
        'notice_time'      => date('Y-m-d H:i:s', $time),
        'notice_timestamp' => $time,
    ];
});

// 中文化失败，应该还差了点东西
// $factory->define(App\Crontab::class, function () {
//     $faker  = Factory::create('zh_CN');
//     $time   = time() + rand(60, 150);
//     $n_time = $time + rand(0, 59);
//     return [
//         'todo'             => $faker->text,
//         'user_id'          => '1298426440',
//         'src_group_id'     => '857053153',
//         'notice_time'      => date('Y-m-d H:i:s', $time),
//         'notice_timestamp' => $time,
//     ];
// });
