<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

$factory->define(App\User::class, function (Faker $faker) {
    $name = $faker->name;
    return [
        'user_login'          => $name,
        'user_pass'           => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'user_nicename'       => $name,
        'user_email'          => $faker->unique()->safeEmail,
        'user_url'            => '',
        'user_registered'     => date("Y-m-d H:i:s"),
        'user_activation_key' => '',
        'user_status'         => 0,
        'display_name'        => $name,
        // 'remember_token' => str_random(10),
    ];
});
