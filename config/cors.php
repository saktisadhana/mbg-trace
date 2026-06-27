<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The React frontend is hosted on GitHub Pages (a different origin), so the
    | API must explicitly allow it. Add/override origins via CORS_ALLOWED_ORIGINS
    | (comma-separated) in the environment.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'https://saktisadhana.github.io,http://localhost:5173'
    )))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // No cookie-based auth on this API, so credentials stay off.
    'supports_credentials' => false,

];
