<?php

return [
    'paths' => ['api/*'],  // remove sanctum/csrf-cookie completely

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5174',
    ],

    'allowed_origins_patterns' => [
        '/^http:\/\/localhost:\d+$/',
        '/^http:\/\/127\.0\.0\.1:\d+$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',   // allow frontend to read JWT token if returned in header
    ],

    'max_age' => 0,

    // CRITICAL: JWT DOES NOT USE COOKIES → MUST BE FALSE
    'supports_credentials' => false,
];
