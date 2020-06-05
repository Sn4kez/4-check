<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\LocationExtension::class, function (Faker\Generator $faker) {
    return [
        'fixed' => $faker->boolean(),
    ];
});
