<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\Models\AbstractModel;

class Book extends AbstractModel
{
    protected const RELATIONS = [
        'author' => [User::class, 'author_id'],
    ];
}
