<?php

namespace App\Models;

use Fwt\Framework\Kernel\Login\UserModel;

class User extends UserModel
{
    protected const RELATIONS = [
        'books' => [
            'class' => Book::class,
            'type' => 'one-to-many',
            'field' => 'author_id',
        ],
    ];
}
