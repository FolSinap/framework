<?php

namespace App\Guards;

use App\Models\Book;
use FW\Kernel\Guards\Guard;

class BooksGuard extends Guard
{
    public function getName(): string
    {
        return 'books';
    }

    public function manage(Book $book): bool
    {
        return $this->getUser()?->id === $book->author_id;
    }
}
