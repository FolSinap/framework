<?php

namespace Fwt\Framework\Kernel\Database;

use Fwt\Framework\Kernel\Database\QueryBuilder\StructureQueryBuilder;

abstract class Migration
{
    private Database $database;
    private StructureQueryBuilder $queryBuilder;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->queryBuilder = $this->database->getStructureQueryBuilder();
    }

    protected function getStructureBuilder(): StructureQueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function execute(): void
    {
        $this->database->execute($this->queryBuilder->getQuery());
    }

    abstract public function up(): void;

    abstract public function down(): void;
}