<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class QueryBuilder implements Builder
{
    protected const SELECT = 'SELECT';
    protected const UPDATE = 'UPDATE';
    protected const DELETE = 'DELETE';
    protected const INSERT = 'INSERT';


    protected Builder $builder;

    protected array $select;
    protected array $insert;
    protected array $update;
    protected string $table;


    protected string $type;


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

    public function insert(array $data, string $into): self
    {
        foreach ($data as $field => $value) {
            $this->params[$field] = $value;
            $this->insert[$field] = ":$field";
        }

        $this->table = $into;
        $this->type = self::INSERT;

        return $this;
    }

    public function setParams(array $params): self
    {
        $this->builder->setParams($params);

        return $this;
    }

    public function getParams(): array
    {
        return $this->builder->getParams();
    }

    public function getQuery(): string
    {
        return $this->builder->getQuery();
    }

    protected function buildInsert(): string
    {
        $columns = '(' . implode(' ,', array_keys($this->insert)) . ')';
        $values = '(' . implode(' ,', array_values($this->insert)) . ')';

        return "INSERT INTO $this->table $columns VALUES $values";
    }
}
