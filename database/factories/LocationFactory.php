<?php 

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Location::class, function (Faker\Generator $faker) {
    return [
    	'name' => $faker->word(),
    	'description' => null,
    	'postalCode' => $faker->postcode,
    	'province' => $faker->state,
    	'city' => $faker->city,
    	'street' => $faker->streetName,
    	'streetNumber' => $faker->buildingNumber,
        'parentId' => null
    ];
});