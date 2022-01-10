<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

class InsertBuilder extends AbstractBuilder
{
    protected string $table;
    protected array $data;

    public function __construct(string $table, array $data)
    {
        $this->table = $table;

        foreach ($data as $field => $value) {
            $this->params[$field] = $value;
            $this->data[$field] = ":$field";
        }
    }

    public function getQuery(): string
    {
        $columns = '(' . implode(' ,', array_keys($this->data)) . ')';
        $values = '(' . implode(' ,', array_values($this->data)) . ')';

        return "INSERT INTO $this->table $columns VALUES $values";
    }
}
