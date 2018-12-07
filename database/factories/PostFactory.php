<?php

use Faker\Generator as Faker;

$factory->define(App\Post::class, function (Faker $faker) {
    $contents    = $faker->text;
    $title       = $faker->text(20);
    $post_status = [
        0 => 'inherit',
        1 => 'published',
        2 => 'draft',
    ];
    $common_status = [
        0 => 'closed',
        1 => 'open',
    ];
    $post_type = [
        0 => 'post',
        1 => 'revision',
    ];
    $time = time();
    return [
        //
        'post_author'           => '1',
        'post_content'          => $contents,
        'post_title'            => $title,
        'post_excerpt'          => substr($contents, 0, 20),
        'post_status'           => $post_status[rand(0, 2)],
        'comment_status'        => $common_status[rand(0, 1)],
        'ping_status'           => $common_status[rand(0, 1)],
        'post_password'         => '',
        'post_name'             => rawurlencode($title),
        'to_ping'               => '',
        'pinged'                => '',
        'post_date'             => date('Y-m-d H:i:s', $time),
        'post_date_gmt'         => gmdate('Y-m-d H:i:s', $time),
        'post_modified'         => date('Y-m-d H:i:s', $time),
        'post_modified_gmt'     => gmdate('Y-m-d H:i:s', $time),
        'post_content_filtered' => '',
        'post_parent'           => '0',
        'guid'                  => '',
        'menu_order'            => '0',
        'post_type'             => $post_type[rand(0, 1)],
        'post_mime_type'        => '',
        'comment_count'         => '0',
    ];
});
