<?php

return [
    'driver' => 'database', //redis, database, files

    'files' => [
        'dir' => 'storage/cache',
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

    'redis' => [
        'host' => '',
        'port' => '',
    ]
];
