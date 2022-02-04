<?php

namespace App\Models;

use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Relation\Relation;

class BookGenre extends Model
{
    protected const ID_COLUMNS = ['book_id', 'genre_id'];
	public const RELATIONS = [
		'book' => [
			'class' => Book::class,
			'field' => 'book_id',
			'type' => Relation::TO_ONE,
		],
		'genre' => [
			'class' => Genre::class,
			'field' => 'genre_id',
			'type' => Relation::TO_ONE,
		],
	];

	protected static array $columns = [
		'book_id',
		'genre_id',
	];

	public static function getTableName(): string
    {
        return 'books_genres';
    }
}
