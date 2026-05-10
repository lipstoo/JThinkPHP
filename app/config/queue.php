<?php

return [
    'driver' => env('QUEUE_DRIVER', 'sync'),
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', ''),
        'database' => env('REDIS_DB', 0),
    ],
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'table' => 'jobs',
    ],
    'sync' => [
        'enabled' => true,
    ],
];
?>