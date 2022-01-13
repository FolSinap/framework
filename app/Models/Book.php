<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Relation;

class Book extends AbstractModel
{
    protected const RELATIONS = [
        'author' => ['class' => User::class, 'field' => 'author_id'],
        'genres' => [
            'class' => Genre::class,
            'field' => 'genre_id',
            'type' => 'many-to-many',
            'pivot' => 'books_genres',
            'defined_by' => 'book_id',
        ],
    ];
}
