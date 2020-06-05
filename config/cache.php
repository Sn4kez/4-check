<?php

return [
    // Default Cache Store
    'default' => env('CACHE_DRIVER', 'memcached'),

    // Cache Stores
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        'array' => [
            'driver' => 'array',
        ],
        'database' => [
            'driver' => 'database',
            'table' => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => env('CACHE_DATABASE_CONNECTION', null),
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],
        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'), 'port' => env('MEMCACHED_PORT', 11211), 'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'default'),
        ],
    ],

    // Cache Key Prefix
    'prefix' => env('CACHE_PREFIX', 'laravel'),
];
