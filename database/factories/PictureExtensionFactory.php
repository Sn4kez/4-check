<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\PictureExtension::class, function (Faker\Generator $faker) {
    return [
        'image' => sprintf('%s.jpg', \Ramsey\Uuid\Uuid::uuid4()->toString()),
        'type' => $faker->randomElement(['media', 'signature']),
    ];
});
