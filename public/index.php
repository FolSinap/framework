<?php

use Fwt\Framework\Kernel\App;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));

$app->run();
