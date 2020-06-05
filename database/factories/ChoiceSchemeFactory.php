<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\ChoiceScheme::class, function (Faker\Generator $faker) {
    return [
        'multiselect' => $faker->boolean(0.2),
    ];
});
