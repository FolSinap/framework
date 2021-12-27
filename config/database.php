<?php

return [
    'driver' => env('DB', 'mysql'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'name' => env('DB_NAME', 'test'),
    'user' => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', 'root'),
];
