<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Checklist::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence(1),
        'numberQuestions' => rand(0, 15),
    ];
});

$factory->state(App\Checklist::class, 'with_description', function (Faker\Generator $faker) {
    return [
        'description' => $faker->sentence(5),
    ];
});
