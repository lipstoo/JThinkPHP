<?php

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'name' => 'JThinkSession',
    'cookie_httponly' => true,
    'cookie_secure' => env('SESSION_SECURE', false),
    'cookie_samesite' => 'Lax',
    'strict_mode' => true,
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'password' => env('REDIS_PASSWORD', ''),
        'database' => env('REDIS_DB', 0),
    ]
];
?>