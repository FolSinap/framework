<?php

use App\Controllers\FormController;
use App\Controllers\LoginController;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

$router = App::$app->getRouter();

$router->get('/', function () {
    return Response::create(View::create('index.php'));
})->middleware('authenticate');

$router->get('/form', [FormController::class, 'show'], 'form_show');
$router->post('/form', [FormController::class, 'process']);

$router->get('/register', [LoginController::class, 'registrationForm']);
$router->post('/register', [LoginController::class, 'register']);
//$router->get('/login', [LoginController::class, 'loginForm']);
//$router->post('/login', [LoginController::class, 'login']);
