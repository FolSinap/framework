<?php

return [
    'channels' => [
        'all' => ['buffer'],
    ],

    'handlers' => [
        'rotating_file' => [
            'type' => 'rotating_file',
            'filename' => storage_dir('logs/rotating.log'),
            'maxFiles' => 10,
            'level' => 'debug',
            'useLocking' => false,
            'filePermission' => 0644,
        ],
        'stream' => [
            'type' => 'stream',
            'stream' => storage_dir('logs/stream.log'),
        ],
        'syslog' => [
            'type' => 'syslog',
            'ident' => 'myfacility',
        ],
        'error_log' => [
            'type' => 'error_log',
        ],
        'redis' => [
            'type' => 'redis',
            'key' => 'logs',
            'formatter' => 'html',
        ],
        'redis_crossed' => [
            'type' => 'fingers_crossed',
            'handler' => 'redis',
            'activationStrategy' => 'warning'
        ],
        'buffer' => [
            'type' => 'buffer',
            'handler' => 'redis',
        ]
    ],
];
