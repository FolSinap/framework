<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Data;

use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;

class InsertManyBuilder extends Builder
{
    protected string $table;
    protected array $data = [];
    protected array $fields = [];

    public function __construct(string $table, array $data)
    {
        $this->table = $table;

        $fields = [];

        if (!($this->isArrayOfArrays($data) && $this->areSameLength($data))) {
            throw new IllegalTypeException($data, ['array[] of same length']);
        }

        foreach ($data as $key => $values) {
            $entry = [];
            $fields = [];

            foreach ($values as $field => $value) {
                if (!in_array($field, $fields)) {
                    $fields[] = $field;
                }

                $fieldKey = $key . "_$field";
                $this->params[$fieldKey] = $value;
                $entry[$fieldKey] = ":$fieldKey";
            }

            $this->data[] = $entry;
        }

        $this->fields = $fields;
    }

    public function getQuery(): string
    {
        $columns = '(' . implode(' ,', $this->fields) . ')';
        $values = [];

        foreach ($this->data as $entry) {
            $values[] = '(' . implode(' ,', $entry) . ')';
        }

        $values = implode(', ', $values);

        return "INSERT INTO $this->table $columns VALUES $values";
    }

    protected function isArrayOfArrays(array $data): bool
    {
        if (count($data) === 0) {
            return false;
        }

        foreach ($data as $array) {
            if (!is_array($array)) {
                return false;
            }
        }

        return true;
    }

    protected function areSameLength(array $data): bool
    {
        $count = count(array_shift($data) ?? []);

        foreach ($data as $array) {
            if ($count !== count($array)) {
                return false;
            }
        }

        return true;
    }
}
