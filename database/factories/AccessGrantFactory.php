<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\AccessGrant::class, function (Faker\Generator $faker) {
    return [
        'view' => $faker->boolean,
        'index' => $faker->boolean,
        'update' => $faker->boolean,
        'delete' => $faker->boolean,
    ];
});
