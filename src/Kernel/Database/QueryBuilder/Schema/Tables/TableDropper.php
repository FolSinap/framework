<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Schema\Tables;

use Fwt\Framework\Kernel\Database\QueryBuilder\IBuilder;

class TableDropper implements IBuilder
{
    protected string $table;
    protected bool $ifExists = false;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function ifExists(bool $ifExists = true): self
    {
        $this->ifExists = $ifExists;

        return $this;
    }

    public function getQuery(): string
    {
        return $this->buildDrop();
    }

    protected function buildDrop(): string
    {
        return 'DROP TABLE' . ($this->ifExists ? ' IF EXISTS' : '') . " $this->table";
    }
}
