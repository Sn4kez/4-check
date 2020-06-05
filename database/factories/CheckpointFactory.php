<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Checkpoint::class, function (Faker\Generator $faker) {
    return [
        'prompt' => $faker->sentence(4),
        'mandatory' => $faker->boolean(),
        'factor' => $faker->randomFloat(2, 0, 1),
        'index' => (string) $faker->numberBetween(1000, 9000),
    ];
});
