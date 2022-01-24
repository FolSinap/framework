<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema;

use Fwt\Framework\Kernel\Database\QueryBuilder\Builder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;
use Fwt\Framework\Kernel\Database\SQL\Query;

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

    public function getQuery(): Query
    {
        return new Query($this->builder->getQuery());
    }
}
