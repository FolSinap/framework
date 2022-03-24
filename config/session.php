<?php

return [
    'driver' => 'database', //null, files, database, redis, memcached, array
    'lifetime' => 15 * 60, //seconds, 15 min by default

    'files' => [
        'dir' => 'storage/session',
    ],

    'database' => [
        'table' => 'sessions',
    ],

    //set to null for default connection from database config
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],

    //set to null for default connection from database config
    'memcached' => [
        'servers' => [
            [
                'host' => 'localhost',
                'port' => 11211,
                'weight' => 100,
            ],
        ],
    ],
];
