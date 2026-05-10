<?php

return [
    'app' => [
        'name' => env('APP_NAME', 'JThink'),
        'env' => env('APP_ENV', 'development'),
        'debug' => env('APP_DEBUG', true),
        'base_url' => env('APP_URL', ''),
        'log_level' => env('APP_LOG_LEVEL', 100),
        'route_cache' => env('ROUTE_CACHE', false),
    ],
    'providers' => [
        // 'App\Providers\AppServiceProvider',
    ],
    'mail' => [
        'host' => env('MAIL_HOST', 'smtp.example.com'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from' => [
            'email' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
            'name' => env('MAIL_FROM_NAME', 'JThink')
        ]
    ],
    'jwt' => [
        'secret' => env('JWT_SECRET', 'jthink_secret_key'),
        'algorithm' => env('JWT_ALGORITHM', 'HS256'),
        'expire' => env('JWT_EXPIRE', 3600)
    ],
    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 2097152),
        'upload_path' => env('UPLOAD_PATH', 'storage/uploads'),
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/docx',
            'application/xlsx'
        ]
    ],
    'view' => [
        'cache_enabled' => env('VIEW_CACHE', false),
        'cache_path' => STORAGE_PATH . '/cache/views',
        'cache_duration' => 3600,
    ],
    'database' => [
        'query_cache' => env('QUERY_CACHE', false),
        'cache_duration' => 3600,
    ],
    'queue' => [
        'driver' => env('QUEUE_DRIVER', 'sync'),
    ]
];
?>