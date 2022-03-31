<?php

namespace App\Migrations;

use FW\Kernel\Database\Migration;

class m0008_create_logs_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('logs');

        $table->id();
        $table->string('channel');
        $table->longText('message');
        $table->int('level');
        $table->timestamp('time');
    }

    public function down(): void
    {
        $this->drop('logs');
    }
}
