<?php

namespace App\Controllers;

use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Response\Response;

class BooksController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('books/index.php');
    }
}
