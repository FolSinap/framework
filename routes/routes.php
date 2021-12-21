<?php

use App\Controllers\FormController;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

$router = App::$app->getRouter();

$router->get('/', function () {
    return Response::create(View::create('index.php'));
});

$router->get('/form', [FormController::class, 'show'], 'form_show');
$router->post('/form', [FormController::class, 'process']);
