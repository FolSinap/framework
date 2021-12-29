<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables;

use Fwt\Framework\Kernel\Database\QueryBuilder\Builder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Columns\ColumnBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Columns\ForeignKeyColumn;

class TableBuilder implements Builder
{
    protected string $table;
    protected bool $ifNotExists = false;
    /** @var ColumnBuilder[] $columns */
    protected array $columns = [];
    protected array $primaryKey = [];
    protected array $uniques = [];
    /** @var ForeignKeyColumn[] $foreignKeys */
    protected array $foreignKeys = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function ifNotExists(bool $ifNotExists = true): self
    {
        $this->ifNotExists = $ifNotExists;

        return $this;
    }

    public function int(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::INT);
        $this->columns[] = $column;

        return $column;
    }

    public function bigInt(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::BIGINT);
        $this->columns[] = $column;

        return $column;
    }

    public function id(string $name = 'id'): ColumnBuilder
    {
        $id = $this->bigInt($name)->autoIncrement();
        $this->primaryKeys([$id->getName()]);

        return $id;
    }

    public function string(string $name, int $length = 255): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::VARCHAR, ['length' => $length]);
        $this->columns[] = $column;

        return $column;
    }

    public function bool(string $name): self
    {
        $this->columns[] = $this->createColumn($name, ColumnBuilder::BIT);

        return $this;
    }

    public function primaryKeys(array $columns): self
    {
        $this->primaryKey = $columns;

        return $this;
    }

    public function getQuery(): string
    {
        $sql = 'CREATE TABLE' . ($this->ifNotExists ? ' IF NOT EXISTS' : '') . " $this->table" . ' (';

        foreach ($this->columns as $column) {
            $sql .= ' ' . $column->buildQuery() . ',';
        }

        foreach ($this->uniques as $column => $index) {
            $sql .= " UNIQUE KEY $index ($column),";
        }

        foreach ($this->foreignKeys as $column) {
            $sql .= ' ' . $column->buildForeign();
        }

        if (!empty($this->primaryKey)) {
            $sql .= ' PRIMARY KEY (' . implode(' ,', $this->primaryKey) . '))';
        } else {
            $sql = rtrim($sql, ',');
        }

        return $sql;
    }

    public function addUnique(ColumnBuilder $column, string $indexName = null): self
    {
        $columnName = $column->getName();
        $this->uniques[$columnName] = $indexName ?? ($columnName . '_unique_index');

        return $this;
    }

    public function addForeign(ForeignKeyColumn $column): self
    {
        $this->foreignKeys[] = $column;

        return $this;
    }

    protected function createColumn(string $name, string $type, array $options = []): ColumnBuilder
    {
        return new ColumnBuilder($this, $name, $type, $options);
    }
}
