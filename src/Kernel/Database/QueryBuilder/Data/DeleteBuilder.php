<?php

namespace FW\Kernel\Database\QueryBuilder\Data;

class DeleteBuilder extends Builder
{
    use UsingWhereStatements;

    protected string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function getQuery(): string
    {
        $sql = "DELETE FROM $this->table";

        $sql .= $this->buildWhere();

        return $sql;
    }
}
