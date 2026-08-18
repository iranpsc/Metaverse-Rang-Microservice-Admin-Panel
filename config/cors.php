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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'translations/*', 'uploads/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://metarang.com',
        'https://world.metarang.com',
        'https://admin.metarang.com',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:5175',
        'http://localhost:5174',
        'https://dev-reactjs.metarang.com',
        'https://dev-nextjs.metarang.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
