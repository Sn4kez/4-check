<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\ArchiveDirectory::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence(1),
    ];
});

$factory->state(App\ArchiveDirectory::class, 'with_description', function (Faker\Generator $faker) {
    return [
        'description' => $faker->sentence(5),
    ];
});
