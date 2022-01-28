<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Database\QueryBuilder\Data\DataBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Data\DeleteBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Data\InsertBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Data\InsertManyBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Data\SelectBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Data\UpdateBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;
use Fwt\Framework\Kernel\Database\SQL\Query;

class QueryBuilder
{
    /** @var DataBuilder|SchemaBuilder $builder */
    protected $builder;

    public static function getBuilder(): self
    {
        return new static();
    }

    public function select(string $from, array $columns = []): SelectBuilder
    {
        $this->setDataBuilder();

        return $this->builder->select($from, $columns);
    }

    public function delete(string $from): DeleteBuilder
    {
        $this->setDataBuilder();

        return $this->builder->delete($from);
    }

    public function update(string $table, array $data): UpdateBuilder
    {
        $this->setDataBuilder();

        return $this->builder->update($table, $data);
    }

    public function insert(string $table, array $data): InsertBuilder
    {
        $this->setDataBuilder();

        return $this->builder->insert($table, $data);
    }

    public function insertMany(string $table, array $data): InsertManyBuilder
    {
        $this->setDataBuilder();

        return $this->builder->insertMany($table, $data);
    }

    public function create(string $tableName): TableBuilder
    {
        $this->setSchemaBuilder();

        return $this->builder->create($tableName);
    }

    public function drop(string $tableName): TableDropper
    {
        $this->setSchemaBuilder();

        return $this->builder->drop($tableName);
    }

    public function alter(string $tableName): TableAlterer
    {
        $this->setSchemaBuilder();

        return $this->builder->alter($tableName);
    }

    public function setParams(array $params): self
    {
        if (method_exists($this->builder, 'setParams')) {
            $this->builder->setParams($params);
        }

        return $this;
    }

    public function getQuery(): Query
    {
        return $this->builder->getQuery();
    }

    protected function setDataBuilder(): void
    {
        $this->builder = DataBuilder::getBuilder();
    }

    protected function setSchemaBuilder(): void
    {
        $this->builder = SchemaBuilder::getBuilder();
    }
}
