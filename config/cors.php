<?php

return [
    // Allow the web dashboards (and any client) to call the API + login.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Bearer-token auth (not cookies), so credentials are not required.
    'supports_credentials' => false,
];
