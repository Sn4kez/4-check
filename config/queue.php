<?php

return [
    // Default Queue Driver
    'default' => env('QUEUE_DRIVER', 'sync'),

    // Queue Connections
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 60,
        ],
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 60,
        ],
        'sqs' => [
            'driver' => 'sqs',
            'key' => 'your-public-key',
            'secret' => 'your-secret-key',
            'queue' => 'your-queue-url',
            'region' => 'us-east-1',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('QUEUE_REDIS_CONNECTION', 'default'),
            'queue' => 'default',
            'retry_after' => 60,
        ],
    ],

    // Failed Queue Jobs
    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => env('QUEUE_FAILED_TABLE', 'failed_jobs'),
    ],
];
