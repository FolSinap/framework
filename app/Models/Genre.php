<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Database\ORM\Relation\AbstractRelation;

class Genre extends Model
{
    protected const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'field' => 'book_id',
            'type' => AbstractRelation::MANY_TO_MANY,
            'pivot' => 'books_genres',
            'defined_by' => 'genre_id',
        ],
    ];
}
