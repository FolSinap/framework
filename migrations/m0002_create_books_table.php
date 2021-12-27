<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0002_create_books_table extends Migration
{
    public function up(): void
    {
        $this->getStructureBuilder()->create('books')
            ->id()
            ->string('title', 100);

        $this->execute();
    }

    public function down(): void
    {
        $this->getStructureBuilder()->drop('books');

        $this->execute();
    }
}
