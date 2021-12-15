<?php

use Fwt\Framework\Kernel\Database\Migration;

class m0001_create_user_table extends Migration
{
    public function up(): void
    {
        $this->database->execute('CREATE TABLE user (
                    id BIGINT NOT NULL,
                    email VARCHAR (30) NOT NULL,
                    name VARCHAR (30),
                    PRIMARY KEY (id)
                  )');
    }

    public function down(): void
    {
        $this->database->execute('DROP TABLE user');
    }
}
