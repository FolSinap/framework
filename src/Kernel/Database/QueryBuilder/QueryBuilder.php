<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class QueryBuilder implements Builder
{
    protected const SELECT = 'SELECT';
    protected const UPDATE = 'UPDATE';
    protected const DELETE = 'DELETE';
    protected const INSERT = 'INSERT';

    protected const WHERE_EXPRESSIONS = ['!=', '<>', '=', '>', '>', '>=', '<=', 'LIKE'];

    protected array $select;
    protected array $insert;
    protected array $update;
    protected string $table;
    protected string $where;
    protected array $andWhere;
    protected array $orWhere;
    protected string $nativeWhere;
    protected array $groupBy;
    protected string $type;
    protected array $params;

    public static function getBuilder(): self
    {
        return new static();
    }

    public function select(array $fields = []): self
    {
        $this->select = $fields;
        $this->type = self::SELECT;

        return $this;
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

    public function delete(): self
    {
        $this->type = self::DELETE;

        return $this;
    }

    public function update(string $table): self
    {
        $this->type = self::UPDATE;
        $this->table = $table;

        return $this;
    }

    public function set(array $data): self
    {
        foreach ($data as $field => $value) {
            $this->params[$field] = $value;
            $this->update[$field] = ":$field";
        }

        return $this;
    }

    public function from(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function where(string $field, string $value, string $expression = '='): self
    {
        $this->where = $this->addWhere($field, $value, $expression);

        return $this;
    }

    public function andWhere(string $field, string $value, string $expression = '='): self
    {
        $this->andWhere[] = $this->addWhere($field, $value, $expression);

        return $this;
    }

    public function orWhere(string $field, string $value, string $expression = '='): self
    {
        $this->orWhere[] = $this->addWhere($field, $value, $expression);

        return $this;
    }

    public function nativeWhere(string $expression): self
    {
        $this->nativeWhere = $expression;

        return $this;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params ?? [];
    }

    public function groupBy(array $fields): self
    {
        $this->groupBy = $fields;

        return $this;
    }

    public function getQuery(): string
    {
        switch ($this->type) {
            case self::UPDATE:
                return $this->buildUpdate();
            case self::INSERT:
                return $this->buildInsert();
            case self::DELETE:
                return $this->buildDelete();
            default:
                return $this->buildSelect();
        }
    }

    protected function addWhere(string $field, string $value, string $expression): string
    {
        if (!in_array($expression, self::WHERE_EXPRESSIONS)) {
            throw new IllegalValueException($expression, self::WHERE_EXPRESSIONS);
        }

        $this->params[$field] = $value;

        return "$field $expression :$field";
    }

    protected function buildWhere(): string
    {
        if (isset($this->nativeWhere)) {
            $sql = " WHERE $this->nativeWhere";
        } elseif (isset($this->where)) {
            $sql = " WHERE $this->where";

            $sql .= isset($this->andWhere) ? ' AND ' . implode(' AND ', $this->andWhere) : '';

            $sql .= isset($this->orWhere) ? ' OR ' . implode(' OR ', $this->orWhere) : '';
        }

        return $sql ?? '';
    }

    protected function buildSelect(): string
    {
        $sql = 'SELECT ' . (empty($this->select) ? '*' : implode(', ', $this->select)) . " FROM $this->table";

        $sql .= $this->buildWhere();

        $sql .= isset($this->groupBy) ? ' GROUP BY ' . implode(', ', $this->groupBy) : '';

        return $sql;
    }

    protected function buildInsert(): string
    {
        $columns = '(' . implode(' ,', array_keys($this->insert)) . ')';
        $values = '(' . implode(' ,', array_values($this->insert)) . ')';

        return "INSERT INTO $this->table $columns VALUES $values";
    }

    protected function buildDelete(): string
    {
        $sql = "DELETE FROM $this->table";

        $sql .= $this->buildWhere();

        return $sql;
    }

    protected function buildUpdate(): string
    {

        $sql = "UPDATE $this->table SET";

        foreach ($this->update as $field => $value) {
            $sql .= " $field = $value,";
        }

        $sql = rtrim($sql, ',');

        $sql .= $this->buildWhere();

        return $sql;
    }
}
