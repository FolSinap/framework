<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Relation\Relation;
use Fwt\Framework\Kernel\Login\UserModel;

class User extends UserModel
{
    protected const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'type' => Relation::ONE_TO_MANY,
            'field' => 'author_id',
        ],
    ];
}
