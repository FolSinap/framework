<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Data;

class UpdateBuilder extends Builder
{
    use UsingWhereStatements;

    protected string $table;
    protected array $data;

    public function __construct(string $table, array $data)
    {
        $this->table = $table;
        $this->set($data);
    }

    public function set(array $data): self
    {
        foreach ($data as $field => $value) {
            $this->params[$field] = $value;
            $this->data[$field] = ":$field";
        }

        return $this;
    }

    public function getQuery(): string
    {
        $sql = "UPDATE $this->table SET";

        foreach ($this->data as $field => $value) {
            $sql .= " $field = $value,";
        }

        $sql = rtrim($sql, ',');

        $sql .= $this->buildWhere();

        return $sql;
    }
}
