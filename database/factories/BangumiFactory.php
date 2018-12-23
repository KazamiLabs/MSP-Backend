<?php

use Faker\Generator as Faker;

$factory->define(App\Bangumi::class, function (Faker $faker) {
    $groups = ['Mabors-Raw', '幻之字幕组', '幻之字幕組'];
    $path   = $faker->file(storage_path('faker/torrent'), storage_path('app/private/torrent'));
    return [
        //
        'filename'   => str_replace(storage_path('app/private/torrent'), '', $path),
        'filepath'   => $path,
        'group_name' => $groups[rand(0, count($groups) - 1)],
        'title'      => $faker->title,
        'year'       => '2018',
    ];
});
