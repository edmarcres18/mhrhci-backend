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
    'allowed_origins' => ['*'], // allow all origins
    'allowed_origins_patterns' => ['*'], // pattern-based allow-all for safety
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
