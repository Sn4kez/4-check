<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\AuditState::class, function (Faker\Generator $faker) {
    return [
        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'name' => $faker->name(20)
    ];
});