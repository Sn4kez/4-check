<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Section::class, function (Faker\Generator $faker) {
    return [
        'title' => ucfirst($faker->domainWord),
        'index' => (string) $faker->numberBetween(1000, 9000),
    ];
});
