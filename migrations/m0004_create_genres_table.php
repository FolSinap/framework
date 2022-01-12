<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0004_create_genres_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('genres');

        $table->id();
        $table->string('name', 30);

        $this->execute();
    }

    public function down(): void
    {
        $this->drop('genres');

        $this->execute();
    }
}
