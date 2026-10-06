<?php

// Never use '*' in production. Set FRONTEND_URL (comma separated for several origins).
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URL', 'http://localhost:5500'))))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 600,
    // Bearer-token auth (Sanctum personal access tokens) needs no cookies.
    'supports_credentials' => false,
];
