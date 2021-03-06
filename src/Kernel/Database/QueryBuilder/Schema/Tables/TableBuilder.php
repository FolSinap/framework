<?php

namespace FW\Kernel\Database\QueryBuilder\Schema\Tables;

use FW\Kernel\Database\QueryBuilder\IBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Columns\ColumnBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Columns\ForeignKeyColumn;

class TableBuilder implements IBuilder
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

    public function tinyText(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::TINYTEXT);
        $this->columns[] = $column;

        return $column;
    }

    public function text(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::TEXT);
        $this->columns[] = $column;

        return $column;
    }

    public function mediumText(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::MEDIUMTEXT);
        $this->columns[] = $column;

        return $column;
    }

    public function longText(string $name): ColumnBuilder
    {
        $column = $this->createColumn($name, ColumnBuilder::LONGTEXT);
        $this->columns[] = $column;

        return $column;
    }

    public function timestamps(): void
    {
        $this->createdAt();
        $this->updatedAt();
    }

    public function createdAt(string $name = 'created_at'): ColumnBuilder
    {
        return $this->timestamp($name, true);
    }

    public function updatedAt(string $name = 'updated_at'): ColumnBuilder
    {
        return $this->timestamp($name, true, true);
    }

    public function timestamp(string $name, bool $updateOnInsert = false, bool $updateOnUpdate = false): ColumnBuilder
    {
        $options = [];

        if ($updateOnUpdate) {
            $options['on_update'] = ColumnBuilder::CURRENT_TIMESTAMP;
        }

        if ($updateOnInsert) {
            $options['default'] = ColumnBuilder::CURRENT_TIMESTAMP;
        }

        $column = $this->createColumn($name, ColumnBuilder::TIMESTAMP, $options);
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
            $sql .= " UNIQUE KEY $index (`$column`),";
        }

        foreach ($this->foreignKeys as $column) {
            $sql .= ' ' . $column->buildForeign() . ',';
        }

        if (!empty($this->primaryKey)) {
            $sql .= ' PRIMARY KEY (' . implode(' ,', $this->primaryKey) . ')';
        } else {
            $sql = rtrim($sql, ',');
        }

        return "$sql)";
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
