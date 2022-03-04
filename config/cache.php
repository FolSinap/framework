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

    //set to null for default connection from database config
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ]
];
