<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Notification::class, function (Faker\Generator $faker) {
    /**
     * Define real timestamps here.
     * They will be between the 2018-01-01 and NOW/time().
     *
     * The datetime for read and updated will - of course - always be later than the creation
     */
    $randomCreatedTS = rand(1514761200, time());
    $randomCreated = date('Y-m-d H:i:s', $randomCreatedTS);
    $randomUpdated = date('Y-m-d H:i:s', $randomCreatedTS + rand(900, 1800)); // updated between 15 and 30 minutes later
    $randomRead = date('Y-m-d H:i:s', $randomCreatedTS + rand(300, 600)); // read between 5 and 10 minutes later

    /**
     * Get random user which we can use as sender and recipient
     */
    $randomUser = \App\User::all()->first();

    /**
     * Generate which user shall receive the notification
     */
    $randomUserId = $randomUser->id;

    /**
     * the sender, currently the same value of the user which receives the message.
     * Because NOW we just have system notifications, which does not show which user has sent it.
     * Later - maybe - we also will have directly user notifications or just wanted to show WHO sent
     * the notification.
     * Actually this is not needed, so... same value
     * We can not use zero, because it is not a valid UUID
     */
    $sender = $randomUserId;

    return [
        'user_id' => $randomUserId,
        'sender_id' => $sender, // zero is currently 'system', we do not need more today
        'link' => '/watch/list/3',
        'title' => $faker->text(200),
        'message' => $faker->text(2000),
        'read' => 0,
        'pushed' => 0,
        'createdAt' => $randomCreated,
        'readAt' => $randomRead,
        'updatedAt' => $randomUpdated
    ];
});