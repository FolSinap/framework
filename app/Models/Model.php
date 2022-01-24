<?php

namespace App\Models;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Relation\AbstractRelation;

class Model extends AbstractModel
{
	protected const RELATIONS = [
		'books' => [
			'class' => Book::class,
			'field' => 'book_id',
			'type' => AbstractRelation::TO_ONE,
		],
	];

	protected static array $columns = [
		'book_id',
		'title',
		'id',
	];
}
