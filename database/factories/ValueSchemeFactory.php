<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\ValueScheme::class, function (Faker\Generator $faker) {
    return [
        'unit' => $faker->word,
    ];
});
