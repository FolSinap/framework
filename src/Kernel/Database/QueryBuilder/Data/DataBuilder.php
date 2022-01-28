<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Data;

use Fwt\Framework\Kernel\Database\SQL\Query;

class DataBuilder
{
    protected Builder $builder;

    public static function getBuilder(): self
    {
        return new static();
    }

    public function select(string $from, array $columns = []): SelectBuilder
    {
        $this->builder = new SelectBuilder($from, $columns);

        return $this->builder;
    }

    public function delete(string $from): DeleteBuilder
    {
        $this->builder = new DeleteBuilder($from);

        return $this->builder;
    }

    public function update(string $table, array $data): UpdateBuilder
    {
        $this->builder = new UpdateBuilder($table, $data);

        return $this->builder;
    }

    public function insert(string $table, array $data): InsertBuilder
    {
        $this->builder = new InsertBuilder($table, $data);

        return $this->builder;
    }

    public function insertMany(string $table, array $data): InsertManyBuilder
    {
        $this->builder = new InsertManyBuilder($table, $data);

        return $this->builder;
    }

    public function setParams(array $params): self
    {
        $this->builder->setParams($params);

        return $this;
    }

    public function getQuery(): Query
    {
        return new Query($this->builder->getQuery(), $this->builder->getParams());
    }
}
