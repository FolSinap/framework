<?php

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Connection;

return [
    Connection::class => [
        'config' => FileConfig::from('database')->toArray(),
    ],
];
