<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0003_add_author_id_column_to_books_table extends Migration
{
    public function up(): void
    {
        $table = $this->alter('books');

        $table->bigInt('author_id')->nullable()->default(null)
            ->references('users', 'id')
            ->onUpdate('CASCADE')->onDelete('CASCADE');

        $this->execute();
    }

    public function down(): void
    {
        $this->alter('books')
            ->dropForeign('users', 'id')
            ->drop('author_id');

        $this->execute();
    }
}
