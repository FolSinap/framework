<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\Books\CreateRequestValidator;
use App\Models\Book;
use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Response\RedirectResponse;
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

    public function create(): Response
    {
        return $this->render('books/create.php');
    }

    public function store(CreateRequestValidator $validator): RedirectResponse
    {
        if (!$validator->validate()) {
            return $this->redirectBack();
        }

        Book::create($validator->getBodyData());

        return $this->redirect('/books');
    }
}
