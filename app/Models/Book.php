<?php

namespace App\Models;

use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Relation\Relation;

class Book extends Model
{
    public const RELATIONS = [
        'author' => ['class' => User::class, 'field' => 'author_id', 'inversed_by' => 'books'],
        'genres' => [
            'class' => Genre::class,
            'field' => 'genre_id',
            'type' => Relation::MANY_TO_MANY,
            'pivot' => 'books_genres',
            'defined_by' => 'book_id',
        ],
    ];

    protected static array $casts = [
        'id' => 'int',
        'author_id' => 'int',
        'title' => 'string',
    ];

    public static function getColumns(): array
    {
        return ['title', 'author_id', 'id'];
    }
}
