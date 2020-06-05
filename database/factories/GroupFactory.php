<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Group::class, function (Faker\Generator $faker) {
    return [
        'name' => ucfirst($faker->domainWord),
    ];
});
