<?php

namespace App\Models;

use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Relation\Relation;

class Genre extends Model
{
    public const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'field' => 'book_id',
            'type' => Relation::MANY_TO_MANY,
            'pivot' => 'books_genres',
            'defined_by' => 'genre_id',
        ],
    ];
}
