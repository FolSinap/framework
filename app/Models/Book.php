<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Relation\AbstractRelation;

class Book extends AbstractModel
{
    protected const RELATIONS = [
        'author' => ['class' => User::class, 'field' => 'author_id'],
        'genres' => [
            'class' => Genre::class,
            'field' => 'genre_id',
            'type' => AbstractRelation::MANY_TO_MANY,
            'pivot' => 'books_genres',
            'defined_by' => 'book_id',
        ],
    ];

    protected static array $columns = ['title', 'author_id', 'id'];
}
