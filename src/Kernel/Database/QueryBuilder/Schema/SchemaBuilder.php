<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema;

use Fwt\Framework\Kernel\Database\QueryBuilder\Builder;

class SchemaBuilder
{
    protected Builder $builder;
    protected string $table;

    public static function getBuilder(): self
    {
        return new static();
    }

    public function create(string $tableName): TableBuilder
    {
        $this->builder = new TableBuilder($tableName);

        return $this->builder;
    }

    public function drop(string $tableName): TableDropper
    {
        $this->builder = new TableDropper($tableName);

        return $this->builder;
    }

    public function alter(string $tableName): TableAlterer
    {
        $this->builder = new TableAlterer($tableName);

        return $this->builder;
    }

    public function getQuery(): string
    {
        return $this->builder->getQuery();
    }
}
