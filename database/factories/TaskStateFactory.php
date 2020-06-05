<?php 

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\TaskState::class, function (Faker\Generator $faker) {
    return [
    	'name' => $faker->realText(16),
    ];
});