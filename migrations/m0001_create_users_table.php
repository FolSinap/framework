<?php

namespace App\Migrations;

use Fwt\Framework\Kernel\Database\Migration;

class m0001_create_users_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('users');

        $table->id();
        $table->string('email', 30)->unique();
        $table->string('password');
        $table->string('token')->nullable();

        $this->execute();
    }

    public function down(): void
    {
        $this->drop('users');

        $this->execute();
    }
}
