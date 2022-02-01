<?php

$projectDir = app()->getProjectDir();

return [
    'app' => [
        'dir' => $projectDir . '/app',
    ],
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
            \Fwt\Framework\Kernel\Middlewares\ValidateCsrfMiddleware::class,
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
    'csrf' => [
        'enable' => true,
        'validator' => \Fwt\Framework\Kernel\Csrf\CsrfValidator::SYNCHRONIZER_TOKENS_PATTERN,
    ],
];
