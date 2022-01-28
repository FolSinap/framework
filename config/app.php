<?php

use Fwt\Framework\Kernel\App;

$projectDir = App::$app->getProjectDir();

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
        'files' => [
            'routes.php',
        ],
    ],
    'models' => [
        'dir' => $projectDir . '/app/Models',
        'namespace' => '\\App\\Models',
    ],
    'middlewares' => [
        'dir' => $projectDir . '/app/Middlewares',
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
];
