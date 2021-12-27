<?php

namespace App\Controllers;

use App\Models\Book;
use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Response\Response;

class BooksController extends AbstractController
{
    public function index(): Response
    {
        $books = [];

        foreach (Book::all() as $book) {
            $books[$book->id] = $book->title;
        }

        return $this->render('books/index.php', compact('books'));
    }
}
