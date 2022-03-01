<?php

namespace App\Models;

use FW\Kernel\Database\ORM\Relation\Relation;
use FW\Kernel\Login\UserModel;

class User extends UserModel
{
    public const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'type' => Relation::ONE_TO_MANY,
            'field' => 'author_id',
            'inversed_by' => 'author',
        ],
    ];

    public static function getColumns(): array
    {
        return ['id', 'email', 'password'];
    }
}
