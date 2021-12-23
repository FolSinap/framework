<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

class StructureQueryBuilder
{
    protected const CREATE = 'CREATE';
    protected const ALTER = 'ALTER';
    protected const DROP = 'DROP';

    protected string $type;
    protected string $create;
    protected string $drop;
    protected bool $ifNotExists = false;
    protected bool $ifExists = false;
    protected array $columns;
    protected array $primaryKey = [];
    protected array $uniques = [];

    public static function getBuilder(): self
    {
        return new static();
    }

    public function create(string $tableName): self
    {
        $this->type = self::CREATE;
        $this->create = $tableName;

        return $this;
    }

    public function drop(string $tableName): self
    {
        $this->type = self::DROP;
        $this->drop = $tableName;

        return $this;
    }

    public function getQuery(): string
    {
        switch ($this->type) {
            default:
                return $this->buildCreate();
            case self::DROP:
                return $this->buildDrop();
        }
    }

    public function ifNotExists(bool $ifNotExists = true): self
    {
        $this->ifNotExists = $ifNotExists;

        return $this;
    }

    public function ifExists(bool $ifExists = true): self
    {
        $this->ifExists = $ifExists;

        return $this;
    }

    public function int(string $name): self
    {
        $this->columns[] = $this->createColumn($name, 'INT');

        return $this;
    }

    public function bigInt(string $name): self
    {
        $this->columns[] = $this->createColumn($name, 'BIGINT');

        return $this;
    }

    public function id(): self
    {
        $this->bigInt('id')->autoIncrement()->primaryKeys(['id']);

        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = $this->createColumn($name, 'VARCHAR', ['length' => $length]);

        return $this;
    }

    public function bool(string $name): self
    {
        $this->columns[] = $this->createColumn($name, 'BIT');

        return $this;
    }

    public function nullable(bool $nullable = true): self
    {
        $this->columns[array_key_last($this->columns)]['options']['nullable'] = $nullable;

        return $this;
    }

    public function default($default): self
    {
        $this->columns[array_key_last($this->columns)]['options']['default'] = $default;

        return $this;
    }

    public function autoIncrement(bool $autoIncrement = true): self
    {
        $this->columns[array_key_last($this->columns)]['options']['auto_increment'] = $autoIncrement;

        return $this;
    }

    public function unique(bool $unique = true, string $indexName = null): self
    {
        $columnName = $this->columns[array_key_last($this->columns)]['name'];
        $this->uniques[$columnName] = $indexName ?? ($columnName . '_unique_index');

        return $this;
    }

    public function primaryKeys(array $columns): self
    {
        $this->primaryKey = $columns;

        return $this;
    }

    protected function buildCreate(): string
    {
        $sql = 'CREATE TABLE' . ($this->ifNotExists ? ' IF NOT EXISTS' : '') . " $this->create" . ' (';

        foreach ($this->columns as $column) {
            $sql .= " $column[name] $column[type]"
            . $this->buildLength($column['options'])
            . $this->buildNullable($column['options'])
            . $this->buildAutoIncrement($column['options'])
            . $this->buildDefault($column['options'])
            . ',';
        }

        foreach ($this->uniques as $column => $index) {
            $sql .= " UNIQUE KEY $index ($column),";
        }

        if (!empty($this->primaryKey)) {
            $sql .= ' PRIMARY KEY (' . implode(' ,', $this->primaryKey) . '))';
        } else {
            $sql = rtrim($sql, ',');
        }

        return $sql;
    }

    protected function buildLength(array $options): string
    {
        return array_key_exists('length', $options) ? ' (' . $options['length'] . ')' : '';
    }

    protected function buildNullable(array $options): string
    {
        return $options['nullable'] ? '' : ' NOT NULL';
    }

    protected function buildAutoIncrement(array $options): string
    {
        return array_key_exists('auto_increment', $options) && $options['auto_increment'] ? ' AUTO_INCREMENT' : '';
    }

    protected function buildDefault(array $options): string
    {
        return array_key_exists('default', $options) ? ' DEFAULT' . $options['default'] : '';
    }

    protected function buildDrop(): string
    {
        return 'DROP TABLE' . ($this->ifExists ? ' IF EXISTS' : '') . " $this->drop";
    }

    protected function createColumn(string $name, string $type, array $options = []): array
    {
        return [
            'name' => $name,
            'type' => $type,
            'options' => array_merge([
                'nullable' => false,
            ], $options),
        ];
    }
}
