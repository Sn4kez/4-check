<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Audit::class, function (Faker\Generator $faker) {
    $randomUser = \App\User::all()->first();

    return [
        'executionDue' => $faker->dateTimeBetween($startDate = 'now', $endDate = '+5 days', $timezone = null)->format('Y-m-d H:i:s'),
        'userId' => $randomUser->id
    ];
});