<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Phone::class, function (Faker\Generator $faker) {
    return [
        'countryCode' => sprintf('%d', rand(1, 99)),
        'nationalNumber' => sprintf('%d', rand(100000, 999999999)),
    ];
});
