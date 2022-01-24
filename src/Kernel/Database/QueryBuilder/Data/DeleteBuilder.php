<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Data;

class DeleteBuilder extends AbstractBuilder
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
