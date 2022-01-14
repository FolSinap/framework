<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use ArrayAccess;
use Countable;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;
use IteratorAggregate;
use ArrayIterator;

class ModelCollection implements ArrayAccess, IteratorAggregate, Countable
{
    protected array $data = [];
    protected array $new = [];
    protected array $deleted = [];

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    public function synchronize()
    {
        //todo: implement
    }

    public function setData(array $data)
    {
        $this->checkType($data);

        $this->data = $data;
    }

    public function add(array $models): self
    {
//        $this->checkType($models);
//
//        foreach ($models as $key => $model) {
//            $models[$key] = ModelWrapper::wrap($model, ModelWrapper::STATE_INSERT);
//        }
//
//        $this->new = array_merge($this->new, $models);
//        $this->data = array_merge($this->data, $models);
//
//        return $this;
    }

    public function clear(): self
    {
//        foreach ($this->data as $model) {
//            $model->setState(ModelWrapper::STATE_DELETE);
//        }
//
//        $this->deleted = $this->data;
//        $this->data = [];
//
//        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
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

    protected function sortById(array $array): array
    {
        $data = [];

        foreach ($array as $model) {
            $data[$model->{$model::getIdColumn()}] = $model;
        }

        return $data;
    }
}
