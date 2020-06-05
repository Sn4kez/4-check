<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Score::class, function (Faker\Generator $faker) {
    return [
        'name' => ucfirst($faker->domainWord),
        'value' => $faker->numberBetween(0, 100),
        'color' => '#000000',
    ];
});
