<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0001_create_user_table extends Migration
{
    public function up(): void
    {
        $this->getStructureBuilder()->create('users')
            ->id()
            ->string('email', 30)
            ->string('name', 30)->nullable();

        $this->execute();
    }

    public function down(): void
    {
        $this->getStructureBuilder()->drop('users');

        $this->execute();
    }
}
