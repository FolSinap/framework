<?php

use App\Controllers\BooksController;
use App\Controllers\LoginController;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

$router = App::$app->getRouter();

$router->get('/', function () {
    return Response::create(View::create('index.php'));
}, 'main')->middleware('authenticate');

$router->get('/books', [BooksController::class, 'index'])->name('books_index');
$router->get('/books/create', [BooksController::class, 'create'])->name('books_create');
$router->post('/books/create', [BooksController::class, 'store'])->name('books_store');
$router->get('/books/edit/{book}', [BooksController::class, 'edit'])->name('books_edit');
$router->patch('/books/edit/{book}', [BooksController::class, 'update'])->name('books_update');

$router->get('/register', [LoginController::class, 'registrationForm'])->name('register_form');
$router->post('/register', [LoginController::class, 'register'])->name('register');
$router->get('/login', [LoginController::class, 'loginForm'])->name('login_form');
$router->post('/login', [LoginController::class, 'login'])->name('login');
$router->get('/logout', [LoginController::class, 'logout'])->name('logout');
