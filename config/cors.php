<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_map('trim', explode(',', env('CORS_ORIGINS', '*'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Content-Type', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
