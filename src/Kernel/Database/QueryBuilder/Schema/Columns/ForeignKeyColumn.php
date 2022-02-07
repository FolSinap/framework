<?php

namespace FW\Kernel\Database\QueryBuilder\Schema\Columns;

use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use FW\Kernel\Exceptions\IllegalValueException;

class ForeignKeyColumn extends ColumnBuilder
{
    public const CASCADE = 'CASCADE';
    public const SET_NULL = 'SET NULL';
    public const RESTRICT = 'RESTRICT';
    public const NO_ACTION = 'NO ACTION';
    public const SET_DEFAULT = 'SET DEFAULT';
    protected const ACTIONS = [self::CASCADE, self::SET_NULL, self::RESTRICT, self::NO_ACTION, self::SET_DEFAULT];

    protected string $referenceTable;
    protected string $referenceColumn;
    protected string $onDelete;
    protected string $onUpdate;
    protected ?string $indexName;

    public function __construct(TableBuilder $table,
                                string $name,
                                string $type,
                                string $referenceTable,
                                string $referenceColumn,
                                string $indexName = null,
                                array $options = []
    ) {
        $this->referenceTable = $referenceTable;
        $this->referenceColumn = $referenceColumn;
        $this->indexName = $indexName;

        parent::__construct($table, $name, $type, $options);
    }

    public function buildForeign(): string
    {
        $index = $this->buildIndex();
        $sql = "CONSTRAINT $index FOREIGN KEY ($this->name) REFERENCES $this->referenceTable ($this->referenceColumn)";

        if (isset($this->onDelete)) {
            $sql .= " ON DELETE $this->onDelete";
        }

        if (isset($this->onUpdate)) {
            $sql .= " ON UPDATE $this->onUpdate";
        }

        return $sql;
    }

    public function onDelete(string $action): self
    {
        IllegalValueException::checkValue($action, self::ACTIONS);
        $this->onDelete = $action;

        return $this;
    }

    public function onUpdate(string $action): self
    {
        IllegalValueException::checkValue($action, self::ACTIONS);
        $this->onUpdate = $action;

        return $this;
    }

    protected function buildIndex(): string
    {
        return $this->indexName ?? $this->referenceTable . '_' . $this->referenceColumn . '_fk';
    }
}
