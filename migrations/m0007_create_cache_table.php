<?php

namespace App\Migrations;

use FW\Kernel\Database\Migration;

class m0007_create_cache_table extends Migration
{
    public function up(): void
    {
        $table = $this->create('cache');

        $table->string('id');
        $table->longText('payload')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->updatedAt();
        $table->primaryKeys(['id']);
    }

    public function down(): void
    {
        $this->drop('cache');
    }
}
