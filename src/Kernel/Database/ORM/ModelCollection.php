<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use ArrayAccess;
use Countable;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;
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

    public function toArray(): array
    {
        return $this->data;
    }

    public static function __set_state($array)
    {
        return new self($array['data']);
    }

    public function initializeAll(): self
    {
        $ids = [];

        foreach ($this->data as $model) {
            $id = $model->{$model::getIdColumn()};

            if (!$id) {
                throw ModelInitializationException::idIsNotSet($model);
            }

            $ids[get_class($model)][] = $id;
        }

        $models = [];

        /** @var AbstractModel $class */
        foreach ($ids as $class => $id) {
            array_push($models, ...$class::whereIn($class::getIdColumn(), $id)->fetch());
        }

        $this->data = $models;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
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
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    protected function checkType(array $data): void
    {
        foreach ($data as $model) {
            if (!$model instanceof AbstractModel) {
                throw new IllegalTypeException($model, [AbstractModel::class]);
            }
        }
    }
}
