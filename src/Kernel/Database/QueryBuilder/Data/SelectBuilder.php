<?php

namespace FW\Kernel\Database\QueryBuilder\Data;

use FW\Kernel\Database\QueryBuilder\Where\Expression;
use FW\Kernel\Database\QueryBuilder\Where\IExpressionBuilder;
use FW\Kernel\Exceptions\IllegalValueException;

class SelectBuilder extends Builder
{
    use UsingWhereStatements;

    public const LEFT_JOIN = 'left';
    public const RIGHT_JOIN = 'right';
    public const INNER_JOIN = 'inner';
    public const CROSS_JOIN = 'cross';

    protected array $columns;
    protected string $from;
    protected array $groupBy;
    protected array $joins = [];
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

    public function leftJoin(string $table, string $on): self
    {
        $this->addJoin(self::LEFT_JOIN, $table, new Expression($on));

        return $this;
    }

    public function rightJoin(string $table, string $on): self
    {
        $this->addJoin(self::RIGHT_JOIN, $table, new Expression($on));

        return $this;
    }

    public function innerJoin(string $table, string $on): self
    {
        $this->addJoin(self::INNER_JOIN, $table, new Expression($on));

        return $this;
    }

    public function crossJoin(string $table): self
    {
        $this->addJoin(self::INNER_JOIN, $table, new Expression(''));

        return $this;
    }

    public function getQuery(): string
    {
        $sql = 'SELECT ' . (empty($this->columns) ? '*' : implode(', ', $this->columns)) . " FROM $this->from";

        $sql .= implode(' ', $this->joins);

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

    protected function addJoin(string $type, string $table, IExpressionBuilder $on): void
    {
        switch ($type) {
            case self::LEFT_JOIN:
                $this->joins[] = " LEFT JOIN $table ON " . $on->build();

                return;
            case self::RIGHT_JOIN:
                $this->joins[] = " RIGHT JOIN $table ON " . $on->build();

                return;
            case self::CROSS_JOIN:
                $this->joins[] = " CROSS JOIN $table";

                return;
            case self::INNER_JOIN:
                $this->joins[] = " INNER JOIN $table ON " . $on->build();

                return;
        }

        throw new IllegalValueException($type, [self::LEFT_JOIN, self::RIGHT_JOIN, self::INNER_JOIN, self::CROSS_JOIN]);
    }
}
