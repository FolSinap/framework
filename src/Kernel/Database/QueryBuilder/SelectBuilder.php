<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

class SelectBuilder extends AbstractBuilder
{
    use UsingWhereStatements;

    protected array $columns;
    protected string $from;
    protected array $groupBy;

    public function __construct(string $from, array $columns = [])
    {
        $this->from = $from;
        $this->columns = $columns;
    }

    public function groupBy(array $fields): self
    {
        $this->groupBy = $fields;

        return $this;
    }

    public function getQuery(): string
    {
        $sql = 'SELECT ' . (empty($this->columns) ? '*' : implode(', ', $this->columns)) . " FROM $this->from";

        $sql .= $this->buildWhere();

        $sql .= isset($this->groupBy) ? ' GROUP BY ' . implode(', ', $this->groupBy) : '';

        return $sql;
    }
}