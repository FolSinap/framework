<?php

namespace Fwt\Framework\Kernel\Database;

abstract class Migration
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    abstract public function up(): void;

    abstract public function down(): void;
}