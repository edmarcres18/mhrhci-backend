<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Adjust the origins to only the frontends that should talk to this API.
    | Credentials are enabled, so wildcards are not allowed.
    |
    */
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        // Production frontend domains
        'https://mhrpci.site',
        'https://www.mhrpci.site',
        // Local dev frontends
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        'http://192.168.1.210:4000',
        'http://192.168.1.210:4001',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
