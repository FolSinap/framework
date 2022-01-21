<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Columns\ForeignKeyColumn;

class m0005_create_books_genres_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('books_genres');

        $table->bigInt('book_id')->references('books', 'id')
            ->onDelete(ForeignKeyColumn::CASCADE)
            ->onUpdate(ForeignKeyColumn::CASCADE);
        $table->bigInt('genre_id')->references('genres', 'id')
            ->onDelete(ForeignKeyColumn::CASCADE)
            ->onUpdate(ForeignKeyColumn::CASCADE);

        $this->execute();
    }

    public function down(): void
    {
        $this->drop('books_genres');

        $this->execute();
    }
}
