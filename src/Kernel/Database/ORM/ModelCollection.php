<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use ArrayAccess;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use IteratorAggregate;
use ArrayIterator;

class ModelCollection implements ArrayAccess, IteratorAggregate
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function initializeAll(): self
    {
        $ids = [];

        foreach ($this->data as $model) {
            if (!$model instanceof AbstractModel) {
                throw new InvalidExtensionException(get_class($model), AbstractModel::class);
            }

            $ids[get_class($model)][] = $model->{$model::getIdColumn()};
        }

        $models = [];

        /** @var AbstractModel $class */
        foreach ($ids as $class => $id) {
            array_push($models, ...$class::where(WhereBuilder::whereIn($class::getIdColumn(), $id)));
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
}
