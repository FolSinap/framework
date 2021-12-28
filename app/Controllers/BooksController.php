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

        return $this->redirect('books_index');
    }

    public function edit(Book $book): Response
    {
        $title = $book->title;
        $id = $book->id;

        return $this->render('/books/edit.php', compact('title', 'id'));
    }

    public function update(CreateRequestValidator $validator, Book $book): RedirectResponse
    {
        if ($validator->validate()) {
            $book->update($validator->getBodyData());

            return $this->redirect('books_index');
        }

        return $this->redirectBack();
    }

    public function delete(Book $book): RedirectResponse
    {
        $book->delete();

        return $this->redirect('books_index');
    }
}
