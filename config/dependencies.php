<?php

use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Database\Connection;

return [
    Connection::class => [
        'config' => FileConfig::from('database')->toArray(),
    ],
];
