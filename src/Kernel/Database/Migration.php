<?php

namespace Fwt\Framework\Kernel\Database;

use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;

abstract class Migration
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getName(): string
    {
        $namespace = explode('\\', static::class);

        return array_pop($namespace);
    }

    protected function create(string $table): TableBuilder
    {
        return $this->database->create($table);
    }

    protected function drop(string $table): TableDropper
    {
        return $this->database->drop($table);
    }

    protected function alter(string $table): TableAlterer
    {
        return $this->database->alter($table);
    }

    protected function execute(): void
    {
        $this->database->execute();
    }

    public function dry(): string
    {
        return $this->database->getQueryBuilder()->getQuery();
    }

    abstract public function up(): void;

    abstract public function down(): void;
}