<?php

return [
    // Authentication Defaults
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
    ],

    // Authentication Guards
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

    // User Providers
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\User::class,
        ],
    ],

    // Resetting Passwords
    'passwords' => [
        //
    ],
];
