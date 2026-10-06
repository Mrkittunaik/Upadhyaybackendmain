<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => true,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => true,
        ],
        // Cloudflare R2 (S3 compatible). Keep the bucket private; serve via temporary URLs.
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
            'url' => env('R2_PUBLIC_URL'),
            'use_path_style_endpoint' => true,
            'visibility' => 'private',
            'throw' => true,
        ],
    ],
    'links' => [public_path('storage') => storage_path('app/public')],
];
