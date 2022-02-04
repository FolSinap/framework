<?php

namespace FW\Kernel\Database\QueryBuilder\Schema;

use FW\Kernel\Database\QueryBuilder\IBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;
use FW\Kernel\Database\SQL\Query;

class SchemaBuilder
{
    protected IBuilder $builder;
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
