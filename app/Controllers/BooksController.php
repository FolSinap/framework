<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\Books\CreateRequestValidator;
use App\Models\Book;
use App\Models\Genre;
use FW\Kernel\Controllers\Controller;
use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\IdentityMap;
use FW\Kernel\Database\ORM\ModelRepository;
use FW\Kernel\Database\ORM\Models\PrimaryKey;
use FW\Kernel\Database\ORM\UnitOfWork;
use FW\Kernel\Database\SQL\SqlLogger;
use FW\Kernel\Response\RedirectResponse;
use FW\Kernel\Response\Response;

class BooksController extends Controller
{
    public function index(Database $database): Response
    {
        SqlLogger::on();
        $uow = UnitOfWork::getInstance();
        $rep = new ModelRepository();
        $book = Book::find(16);
        $rep->delete($book);
        dd($book);
//        $uow->registerNew(Book::find(1));
        $uow->commit();
        dd($uow);
//        IdentityMap::getInstance()->add(Book::createDry(['title' => 'asd']));
//        IdentityMap::getInstance()->add(Book::createDry(['title' => 'aфывsd']));
//        dd($map = IdentityMap::getInstance());
        $repository = new ModelRepository();
//        $map->add($book = new Book());
//        $map->add(Book::createDry(['id'=>1]));
//        $key = new PrimaryKey(['id'=>1]);
//        dd($map->find(Book::class ,['id'=>1]), $map, $key);
        dump(Book::find(4), Book::whereIn('id', [4,5,1])->fetch());
        dd(IdentityMap::getInstance());
        dd(SqlLogger::getLogger());
        dump($map);
        $map->delete($book);
        dump($map);
        $map->clear();
        dd($map);
        $books = Book::all(['author', 'genres']);
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
