<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\Books\CreateRequestValidator;
use App\Models\Book;
use App\Models\Genre;
use FW\Kernel\Controllers\Controller;
use FW\Kernel\Response\RedirectResponse;
use FW\Kernel\Response\Response;

class BooksController extends Controller
{
    public function index(): Response
    {
        $books = Book::all(['author', 'genres']);
        $user = $this->getUser();

        return $this->render('books/index', compact('books', 'user'));
    }

    public function create(): Response
    {
        $genres = Genre::all();

        return $this->render('books/create', compact('genres'));
    }

    public function store(CreateRequestValidator $validator): RedirectResponse
    {
        if (!$validator->validate()) {
            return $this->redirectBack();
        }

        $user = $this->getUser();
        $body = $validator->getBodyData();

        if (array_key_exists('genres', $body)) {
            $genres = Genre::fromIds($body['genres']);
            unset($body['genres']);
        }

        $book = Book::createDry($body);
        $book->author = $user;
        $book->genres = $genres ?? null;

        $book->insert();

        return $this->redirect('books_index');
    }

    public function edit(Book $book): Response
    {
        $genres = Genre::all();
        $bookGenreIds = $book->genres->map(function ($genre) {
            return $genre->id;
        });

        return $this->render('/books/edit', compact('book', 'genres', 'bookGenreIds'));
    }

    public function update(CreateRequestValidator $validator, Book $book): RedirectResponse
    {
        if ($validator->validate()) {
            $body = $validator->getBodyData();

            if (array_key_exists('genres', $body)) {
                $genres = Genre::fromIds($body['genres']);
                $book->genres = $genres;
            } else {
                $book->genres = null;
            }

            $book->update($body);

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
