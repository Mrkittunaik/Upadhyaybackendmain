<?php

return [
    // API is consumed from a separate frontend origin using Bearer tokens.
    'stateful' => [],
    'guard' => [],
    'expiration' => env('SANCTUM_EXPIRATION') ? (int) env('SANCTUM_EXPIRATION') : null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [],
];
