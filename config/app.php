<?php

$projectDir = project_dir();

return [
    'env' => env('ENV', 'dev'),

    'migrations' => [
        'dir' => $projectDir . '/migrations',
        'namespace' => '\\App\\Migrations',
    ],
    'routes' => [
        'dir' => $projectDir . '/routes',
    ],
    'models' => [
        'dir' => $projectDir . '/app/Models',
        'namespace' => '\\App\\Models',
    ],
    'middlewares' => [
        'dir' => $projectDir . '/app/Middlewares',
        'default' => [
            \FW\Kernel\Middlewares\ValidateCsrfMiddleware::class,
        ],
    ],
    'public' => [
        'dir' => $projectDir . '/public',
    ],
    'templates' => [
        'dir' => $projectDir . '/templates',
    ],
    'commands' => [
        'dir' => $projectDir . '/app/Commands',
        'namespace' => '\\App\\Commands',
    ],
    'guards' => [
        'dir' => $projectDir . '/app/Guards',
        'namespace' => '\\App\\Guards',
    ],
    'csrf' => [
        'enable' => true,
        'validator' => \FW\Kernel\Csrf\CsrfValidator::SYNCHRONIZER_TOKENS_PATTERN,
    ],
];
