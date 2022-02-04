<?php

namespace FW\Kernel\Database\QueryBuilder;

use FW\Kernel\Database\QueryBuilder\Data\DataBuilder;
use FW\Kernel\Database\QueryBuilder\Data\DeleteBuilder;
use FW\Kernel\Database\QueryBuilder\Data\InsertBuilder;
use FW\Kernel\Database\QueryBuilder\Data\InsertManyBuilder;
use FW\Kernel\Database\QueryBuilder\Data\SelectBuilder;
use FW\Kernel\Database\QueryBuilder\Data\UpdateBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;
use FW\Kernel\Database\SQL\Query;

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
