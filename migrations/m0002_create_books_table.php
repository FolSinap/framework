<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0002_create_books_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('books');

        $table->id();
        $table->string('title', 100);
    }

    public function down(): void
    {
        $this->drop('books');
    }
}
