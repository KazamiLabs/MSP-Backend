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
    $name     = $faker->name;
    $userName = $faker->userName;
    return [
        'name'     => $userName,
        'email'    => $faker->unique()->safeEmail,
        'password' => 'secret',
        'nicename' => $name,
        'avatar'   => '',
        'timezone' => $faker->timezone,
        'status'   => 1,
    ];
});
