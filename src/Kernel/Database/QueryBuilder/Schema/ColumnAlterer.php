<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema;

class ColumnAlterer extends ColumnBuilder
{
    protected bool $isNew = true;
    protected string $oldName;

    public function change(): self
    {
        $this->isNew = false;

        return $this;
    }

    public function rename(string $name): self
    {
        $this->oldName = $this->name;
        $this->name = $name;

        return $this;
    }

    public function dropUnique(): self
    {
        $this->isUnique = false;
        $this->table->dropUnique($this);

        return $this;
    }

    public function buildQuery(): string
    {
        if ($this->isNew) {
            return "ADD COLUMN " . parent::buildQuery();
        }

        if (isset($this->oldName)) {
            return "CHANGE $this->oldName" . parent::buildQuery();
        }

        return "MODIFY" . parent::buildQuery();
    }
}
