<?php

use Fwt\Framework\Kernel\Console\App;

require_once __DIR__ . '/vendor/autoload.php';

$app = new App(__DIR__, $argv, $argc);

$app->run();
