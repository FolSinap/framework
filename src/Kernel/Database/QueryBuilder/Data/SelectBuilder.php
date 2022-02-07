<?php

namespace FW\Kernel\Database\QueryBuilder\Data;

class SelectBuilder extends Builder
{
    use UsingWhereStatements;

    protected array $columns;
    protected string $from;
    protected array $groupBy;
    protected int $limit;

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

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getQuery(): string
    {
        $sql = 'SELECT ' . (empty($this->columns) ? '*' : implode(', ', $this->columns)) . " FROM $this->from";

        $sql .= $this->buildWhere();

        $sql .= isset($this->groupBy) ? ' GROUP BY ' . implode(', ', $this->groupBy) : '';

        $sql .= $this->buildLimit();

        return $sql;
    }

    protected function buildLimit(): string
    {
        if (isset($this->limit)) {
            return " LIMIT $this->limit";
        }

        return '';
    }
}
