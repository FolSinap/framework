<?php

return [
    'driver' => env('DB', 'mysql'),

    'drivers' => [
        'mysql' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'name' => env('DB_NAME', 'test'),
            'user' => env('DB_USER', 'root'),
            'password' => env('DB_PASSWORD', 'root'),
        ],

        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
        ],

        'memcached' => [
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
    ],
];
