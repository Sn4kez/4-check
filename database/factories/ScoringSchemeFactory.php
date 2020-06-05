<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\ScoringScheme::class, function (Faker\Generator $faker) {
    return [
        'name' => ucfirst($faker->domainWord),
        'isListed' => true,
    ];
});
