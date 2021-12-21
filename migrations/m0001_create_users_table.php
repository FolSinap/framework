<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0001_create_users_table extends Migration
{
    public function up(): void
    {
        $this->getStructureBuilder()->create('users')
            ->id()
            ->string('email', 30)->unique()
            ->string('password')
            ->string('token')->nullable();

        $this->execute();
    }

    public function down(): void
    {
        $this->getStructureBuilder()
            ->drop('users');

        $this->execute();
    }
}
