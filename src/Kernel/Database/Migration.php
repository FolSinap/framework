<?php

namespace Fwt\Framework\Kernel\Database;

use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;

abstract class Migration
{
    private Database $database;
    private SchemaBuilder $queryBuilder;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->queryBuilder = $this->database->getStructureQueryBuilder();
    }

    public function getName(): string
    {
        $namespace = explode('\\', static::class);

        return array_pop($namespace);
    }

    protected function create(string $table): TableBuilder
    {
        return $this->queryBuilder->create($table);
    }

    protected function drop(string $table): TableDropper
    {
        return $this->queryBuilder->drop($table);
    }

    protected function alter(string $table): TableAlterer
    {
        return $this->queryBuilder->alter($table);
    }

    protected function getStructureBuilder(): SchemaBuilder
    {
        return $this->queryBuilder;
    }

    protected function execute(): void
    {
        $this->database->executeQuery($this->queryBuilder->getQuery());
    }

    abstract public function up(): void;

    abstract public function down(): void;
}