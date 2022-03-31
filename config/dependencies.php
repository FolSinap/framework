<?php

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Connection;

return [
    Connection::class => [
        'config' => config('database')->toArray(),
    ],
];
