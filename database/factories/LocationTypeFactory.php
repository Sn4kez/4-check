<?php 

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\LocationType::class, function (Faker\Generator $faker) {
    return [
    	'name' => $faker->realText(16),
    ];
});