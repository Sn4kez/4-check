<?php 

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\LocationState::class, function (Faker\Generator $faker) {
    return [
    	'name' => $faker->realText(16),
    ];
});