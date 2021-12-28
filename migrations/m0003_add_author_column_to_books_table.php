<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0003_add_author_column_to_books_table extends Migration
{
    public function up(): void
    {
        $table = $this->alter('books');

        $table->string('author');

        $this->execute();
    }

    public function down(): void
    {
        $this->alter('books')->drop('author');

        $this->execute();
    }
}
