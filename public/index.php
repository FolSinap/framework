<?php

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));

$app->getRouter()->get('/', function () {
    return Response::create(View::create('index.php'));
});

$app->getRouter()->get('/form', [\Fwt\Framework\Kernel\Controllers\FormController::class, 'show'], 'form_show');
$app->getRouter()->post('/form', [\Fwt\Framework\Kernel\Controllers\FormController::class, 'process']);

$app->run();
