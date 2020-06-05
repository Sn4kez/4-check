<?php 

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Task::class, function (Faker\Generator $faker) {
    return [
    	'name' => $faker->word(),
    	'description' => $faker->realText(250),
    	'giveNotice' => rand(0,1),
    	'doneAt' => $faker->dateTimeBetween($startDate = 'now', $endDate = '+5 days', $timezone = null)->format('Y-m-d H:i:s'),
    	'assignedAt' => $faker->dateTimeBetween($startDate = 'now', $endDate = '+8 days', $timezone = null)->format('Y-m-d H:i:s'),
    ];
});