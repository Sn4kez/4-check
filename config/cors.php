<?php

return [
    // Laravel CORS
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedHeaders' => ['Authorization', 'Content-Type', 'X-Requested-With'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 0,
];
