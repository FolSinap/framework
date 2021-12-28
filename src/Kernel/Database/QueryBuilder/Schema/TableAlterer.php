<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema;

class TableAlterer extends TableBuilder
{
    protected array $drops = [];
    protected array $renames = [];
    protected array $dropUniques = [];

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

    public function getQuery(): string
    {
        $sql = "ALTER TABLE $this->table";

        foreach ($this->drops as $column) {
            $sql .= " DROP COLUMN $column,";
        }

        if (!empty($this->uniques)) {
            $sql .= 'ADD UNIQUE ([' . implode(', ', $this->uniques) . ']),';
        }

        foreach ($this->dropUniques as $dropUnique) {
            $sql .= "DROP INDEX $dropUnique,";
        }

        return rtrim($sql, ',');
    }
}
