<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'doctor/*', 'inventory/*', 'pos/*', 'login', 'logout', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_filter(array_merge(
        [
            'http://localhost:5173',
            'http://localhost:5178',
            'http://127.0.0.1:5173',
            'http://localhost:5174',
            'http://127.0.0.1:5174',
            'http://localhost:3000',
            'https://admin.bioglowsolutions.com',
            'https://bioglowsolutions.com',
            'https://catalog.bioglowsolutions.com',
            'https://pos.bioglowsolutions.com',
            'https://inventory.bioglowsolutions.com',
            'https://doctor.bioglowsolutions.com',
            'https://staff.bioglowsolutions.com',
            'https://user.bioglowsolutions.com',
        ],
        array_map(
            static fn (string $origin): string => trim($origin),
            explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
        )
    )))),

    // Any localhost / 127.0.0.1 port (Vite :5173, :5180, etc.) for credentialed POS dev.
    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
