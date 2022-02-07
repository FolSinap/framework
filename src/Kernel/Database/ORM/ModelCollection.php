<?php

namespace FW\Kernel\Database\ORM;

use ArrayAccess;
use Countable;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Exceptions\IllegalTypeException;
use IteratorAggregate;
use ArrayIterator;

class ModelCollection implements ArrayAccess, IteratorAggregate, Countable
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    public function setData(array $data)
    {
        $this->checkType($data);

        $this->data = $data;
    }

    public function add(Model $model): self
    {
        $this->data[] = $model;

        return $this;
    }

    public function addMany(self $models): self
    {
        array_push($this->data, ...$models->toArray());

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function map(callable $function): array
    {
        return array_map($function, $this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    protected function checkType(array $data): void
    {
        foreach ($data as $model) {
            if (!$model instanceof Model) {
                throw new IllegalTypeException($model, [Model::class]);
            }
        }
    }

    public static function __set_state($array)
    {
        return new self($array['data']);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }
}
