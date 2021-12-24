<?php

use Fwt\Framework\Kernel\Database\Connection;

return [
    Connection::class => [
        'db' => env('DB', 'mysql'),
        'dbHost' => env('DB_HOST', '127.0.0.1'),
        'dbName' => env('DB_NAME', 'test'),
        'user' => env('DB_USER', 'root'),
        'password' => env('DB_PASSWORD', 'root'),
    ],
];
