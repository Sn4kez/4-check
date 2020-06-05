<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Address::class, function (Faker\Generator $faker) {
    return [
        //
    ];
});

$factory->state(App\Address::class, 'with_line1', function (Faker\Generator $faker) {
    $streetAddress = '';
    if (rand(0, 1) == 0) {
        $streetAddress .= $faker->streetName . ' ' . $faker->buildingNumber;
    } else {
        $streetAddress .= $faker->buildingNumber . ' ' . $faker->streetName;
    }
    return [
        'line1' => $streetAddress,
    ];
});

$factory->state(App\Address::class, 'with_line2', function (Faker\Generator $faker) {
    return [
        'line2' => $faker->secondaryAddress,
    ];
});

$factory->state(App\Address::class, 'with_city', function (Faker\Generator $faker) {
    return [
        'city' => $faker->city,
    ];
});

$factory->state(App\Address::class, 'with_postal_code', function (Faker\Generator $faker) {
    return [
        'postalCode' => $faker->postcode,
    ];
});

$factory->state(App\Address::class, 'with_province', function (Faker\Generator $faker) {
    return [
        'province' => $faker->state,
    ];
});
