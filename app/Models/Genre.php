<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Relation;

class Genre extends AbstractModel
{
    protected const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'field' => 'book_id',
            'type' => 'many-to-many',
            'pivot' => 'books_genres',
            'defined_by' => 'genre_id',
        ],
    ];
}
