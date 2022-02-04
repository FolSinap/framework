<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\Books\CreateRequestValidator;
use App\Models\Book;
use App\Models\Genre;
use FW\Kernel\Controllers\Controller;
use FW\Kernel\Database\Database;
use FW\Kernel\Database\QueryBuilder\QueryBuilder;
use FW\Kernel\Database\SQL\SqlLogger;
use FW\Kernel\Response\RedirectResponse;
use FW\Kernel\Response\Response;
use PDO;

class BooksController extends Controller
{
    public function index(Database $database): Response
    {
//        ;
//        dd($database->executeNative('SELECT books.id as b_id,
//       users.id as u_id,
//       users.email as u_email,
//       users.password as u_password,
//       books.title as b_title,
//       books.author_id as b_author_id
//FROM books LEFT JOIN users ON users.id = books.author_id')
//            ->fetchAll( PDO::FETCH_ASSOC));

        SqlLogger::on();

        Book::all(['author', 'genres']);

        dd(SqlLogger::getLogger());

        $books = Book::all();
        $user = $this->getUser();

        return $this->render('books/index.php', compact('books', 'user'));
    }

    public function create(): Response
    {
        $genres = Genre::all();

        return $this->render('books/create.php', compact('genres'));
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

        return $this->render('/books/edit.php', compact('book', 'genres', 'bookGenreIds'));
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
