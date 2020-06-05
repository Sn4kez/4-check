<?php

return [
    // Default Broadcaster
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    // Broadcast Connections
    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_KEY'),
            'secret' => env('PUSHER_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],
        'log' => [
            'driver' => 'log',
        ],
    ],
];
