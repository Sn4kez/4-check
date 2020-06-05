<?php

return [
    'stripe' => [
        'model' => App\Company::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];