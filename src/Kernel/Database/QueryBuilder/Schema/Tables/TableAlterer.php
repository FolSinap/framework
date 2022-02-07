<?php

namespace FW\Kernel\Database\QueryBuilder\Schema\Tables;

use FW\Kernel\Database\QueryBuilder\Schema\Columns\ColumnAlterer;

class TableAlterer extends TableBuilder
{
    protected array $drops = [];
    protected array $renames = [];
    protected array $dropUniques = [];
    protected array $dropForeigns = [];

    public function drop(string $column): self
    {
        $this->drops[] = $column;

        return $this;
    }

    public function rename(string $old, string $new): self
    {
        $this->renames[$old] = $new;

        return $this;
    }

    public function createColumn(string $name, string $type, array $options = []): ColumnAlterer
    {
        return new ColumnAlterer($this, $name, $type, $options);
    }

    public function dropUnique(ColumnAlterer $column): self
    {
        $this->dropUniques[] = $column->getName();

        return $this;
    }

    public function dropForeign(string $table, string $column): self
    {
        $this->dropForeigns[] = $table . '_' . $column . '_fk';

        return $this;
    }

    public function dropForeignIndex(string $index): self
    {
        $this->dropForeigns[] = $index;

        return $this;
    }

    public function getQuery(): string
    {
        $sql = "ALTER TABLE $this->table";

        foreach ($this->columns as $column) {
            $sql .= ' ' . $column->buildQuery() . ',';
        }

        foreach ($this->dropForeigns as $index) {
            $sql .= " DROP FOREIGN KEY $index,";
        }

        if (!empty($this->uniques)) {
            $sql .= 'ADD UNIQUE ([' . implode(', ', $this->uniques) . ']),';
        }

        foreach ($this->drops as $column) {
            $sql .= " DROP COLUMN $column,";
        }

        foreach ($this->foreignKeys as $column) {
            $sql .= ' ADD ' .  $column->buildForeign() . ',';
        }

        foreach ($this->dropUniques as $dropUnique) {
            $sql .= "DROP INDEX $dropUnique,";
        }

        return rtrim($sql, ',');
    }
}
