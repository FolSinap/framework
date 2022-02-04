<?php

namespace App\Migrations;

use FW\Kernel\Database\Migration;

class m0006_create_sessions_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('sessions');

        $table->string('id');
        $table->mediumText('payload');
        $table->updatedAt();
        $table->primaryKeys(['id']);
    }

    public function down(): void
    {
        $this->drop('sessions');
    }
}
