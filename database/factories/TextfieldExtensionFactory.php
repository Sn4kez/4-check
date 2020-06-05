<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\TextfieldExtension::class, function (Faker\Generator $faker) {
    return [
        'text' => $faker->sentence(10),
        'fixed' => $faker->boolean(),
    ];
});
