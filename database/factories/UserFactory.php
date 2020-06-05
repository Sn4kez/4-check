<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('secret'),
    ];
});

$factory->state(App\User::class, 'with_first_name', function (Faker\Generator $faker) {
    return [
        'firstName' => $faker->firstName,
    ];
});

$factory->state(App\User::class, 'with_middle_name', function (Faker\Generator $faker) {
    return [
        'middleName' => $faker->firstName,
    ];
});

$factory->state(App\User::class, 'with_last_name', function (Faker\Generator $faker) {
    return [
        'lastName' => $faker->lastName,
    ];
});
