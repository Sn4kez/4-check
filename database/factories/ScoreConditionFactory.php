<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\ScoreCondition::class, function (Faker\Generator $faker) {
    return [
        'from' => $faker->randomFloat(2, 1, 50),
        'to' => $faker->randomFloat(2, 50, 100),
    ];
});
