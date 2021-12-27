<?php

use Fwt\Framework\Kernel\App;

$projectDir = App::$app->getProjectDir();

return [
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
    'app' => [
        'dir' => $projectDir . '/app',
    ],
    'public' => [
        'dir' => $projectDir . '/public',
    ],
    'templates' => [
        'dir' => $projectDir . '/templates',
    ],
];
