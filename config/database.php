<?php

return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_NAME', 'jthink'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', ''),
            'charset' => 'utf8mb4',
            'persistent' => false
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('PG_HOST', '127.0.0.1'),
            'port' => env('PG_PORT', '5432'),
            'database' => env('PG_NAME', 'jthink'),
            'username' => env('PG_USER', 'postgres'),
            'password' => env('PG_PASS', ''),
            'persistent' => false
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => BASE_PATH . '/storage/database.sqlite',
            'persistent' => false
        ],

        'sqlserver' => [
            'driver' => 'sqlserver',
            'host' => env('MSSQL_HOST', '127.0.0.1'),
            'port' => env('MSSQL_PORT', '1433'),
            'database' => env('MSSQL_NAME', 'jthink'),
            'username' => env('MSSQL_USER', 'sa'),
            'password' => env('MSSQL_PASS', ''),
            'charset' => 'utf8',
            'persistent' => false
        ]
    ],

    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'password' => env('REDIS_PASS', ''),
        'database' => env('REDIS_DB', '0'),
        'prefix' => env('REDIS_PREFIX', 'jthink:'),
        'timeout' => 0,
        'persistent' => false,
        'persistent_id' => 'jthink_redis'
    ]
];
