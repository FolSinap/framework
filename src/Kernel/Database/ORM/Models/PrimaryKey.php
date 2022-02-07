<?php

namespace FW\Kernel\Database\ORM\Models;

use FW\Kernel\Exceptions\RequiredArrayKeysException;

class PrimaryKey
{
    protected array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function isComposite(): bool
    {
        if (count($this->values) > 1) {
            return true;
        }

        return false;
    }

    public function setValue(string $column, $value)
    {
        $this->values[$column] = $value;
    }

    public function setValues(array $values): self
    {
        RequiredArrayKeysException::checkKeysExistence(array_keys($this->values), $values);

        foreach ($this->values as $column => $value) {
            $this->values[$column] = $values[$column];
        }

        return $this;
    }

    public function getColumns(): array
    {
        return array_keys($this->values);
    }
}
