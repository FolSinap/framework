<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Columns;

use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;

class ColumnBuilder
{
    public const INT = 'INT';
    public const BIGINT = 'BIGINT';
    public const VARCHAR = 'VARCHAR';
    public const BIT = 'BIT';
    public const TYPES = [self::INT, self::BIGINT, self::VARCHAR, self::BIT];

    protected TableBuilder $table;
    protected string $name;
    protected string $type;
    protected bool $isUnique = false;
    protected array $options = ['nullable' => false];

    public function __construct(TableBuilder $table, string $name, string $type, array $options = [])
    {
        $this->table = $table;
        $this->name = $name;
        $this->type = $type;
        $this->options = array_merge($this->options, $options);
    }

    public function buildQuery(): string
    {
        return "$this->name $this->type"
        . $this->buildLength()
        . $this->buildNullable()
        . $this->buildAutoIncrement()
        . $this->buildDefault();
    }

    public function references(string $table, string $column, string $index = null): ForeignKeyColumn
    {
        $foreign = new ForeignKeyColumn($this->table, $this->name, $this->type, $table, $column, $index, $this->options);
        $this->table->addForeign($foreign);

        return $foreign;
    }

    public function autoIncrement(bool $autoIncrement = true): self
    {
        $this->options['auto_increment'] = $autoIncrement;

        return $this;
    }

    public function default($default): self
    {
        $default = is_null($default) ? 'NULL' : $default;

        $this->options['default'] = $default;

        return $this;
    }

    public function nullable(bool $nullable = true): self
    {
        $this->options['nullable'] = $nullable;

        return $this;
    }

    public function unique(bool $unique = true, string $indexName = null): self
    {
        $this->isUnique = $unique;
        $this->table->addUnique($this, $indexName);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function buildLength(): string
    {
        return array_key_exists('length', $this->options) ? ' (' . $this->options['length'] . ')' : '';
    }

    protected function buildNullable(): string
    {
        return $this->options['nullable'] ? '' : ' NOT NULL';
    }

    protected function buildAutoIncrement(): string
    {
        return array_key_exists('auto_increment', $this->options) && $this->options['auto_increment'] ? ' AUTO_INCREMENT' : '';
    }

    protected function buildDefault(): string
    {
        return array_key_exists('default', $this->options) ? ' DEFAULT ' . $this->options['default'] : '';
    }
}
