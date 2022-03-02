<?php

use App\Controllers\BooksController;
use App\Controllers\LoginController;
use FW\Kernel\App;
use FW\Kernel\View\View;
use FW\Kernel\Response\Response;

$router = app()->getRouter();

$router->get('/', function () {
    return Response::create(View::create('index.php'));
}, 'main')->middleware('authenticate');

$router->get('/books', [BooksController::class, 'index'])->name('books_index');
$router->get('/books/create', [BooksController::class, 'create'])->name('books_create')->middleware('authenticate');
$router->post('/books/create', [BooksController::class, 'store'])->name('books_store')->middleware('authenticate');
$router->get('/books/edit/{book}', [BooksController::class, 'edit'])
    ->name('books_edit')
    ->middleware('authenticate')
    ->guard('books:manage');
$router->put('/books/edit/{book}', [BooksController::class, 'update'])
    ->name('books_update')
    ->middleware('authenticate')
    ->guard('books:manage');
$router->delete('/books/delete/{book}', [BooksController::class, 'delete'])
    ->name('books_delete')
    ->middleware('authenticate')
    ->guard('books:manage');

$router->get('/register', [LoginController::class, 'registrationForm'])->name('register_form');
$router->post('/register', [LoginController::class, 'register'])->name('register');
$router->get('/login', [LoginController::class, 'loginForm'])->name('login_form');
$router->post('/login', [LoginController::class, 'login'])->name('login');
$router->get('/logout', [LoginController::class, 'logout'])->name('logout');
