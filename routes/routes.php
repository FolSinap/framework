<?php

use App\Controllers\BooksController;
use App\Controllers\LoginController;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

$router = App::$app->getRouter();

$router->get('/', function () {
    return Response::create(View::create('index.php'));
})->middleware('authenticate');

$router->get('/books', [BooksController::class, 'index']);

$router->get('/register', [LoginController::class, 'registrationForm']);
$router->post('/register', [LoginController::class, 'register']);
$router->get('/login', [LoginController::class, 'loginForm']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
