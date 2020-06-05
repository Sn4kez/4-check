<?php

return [
    // Encryption Key
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    // Application Locale Configuration
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    // Application Timezone Configuration
    'timezone' => 'Europe/Berlin',

    // API URL prefix
    'api_prefix' => 'v2',

    //define standard items per page value for pagination
    'items_per_page' => 15,

    //Mail Sender Information
    'mail_name' => "4-check",
    'mail_address' => "noreply@4-check.com",

    //website
    'url' => env('APP_URL'), 
];
