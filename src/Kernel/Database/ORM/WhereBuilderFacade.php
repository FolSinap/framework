<?php

namespace FW\Kernel\Database\ORM;

use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\QueryBuilder\Data\SelectBuilder;
use FW\Kernel\Exceptions\InvalidExtensionException;
use ReflectionProperty;

class WhereBuilderFacade
{
    public function __construct(
        protected Database $database,
        protected SelectBuilder $builder,
        protected string $class
    ) {
        if (!is_subclass_of($class, Model::class)) {
            throw new InvalidExtensionException($class, Model::class);
        }
    }

    public function fetch(): ModelCollection
    {
        $models = new ModelCollection($this->database->fetchAsObject($this->class));
        $map = IdentityMap::getInstance();

        foreach ($models as $key => $model) {
            if ($map->isManaged($model)) {
                $models[$key] = $map->find($model::class, $model->getPrimaryKey());

                unset($model);
            } else {
                UnitOfWork::getInstance()->registerClean($model);
            }
        }

        $this->setExists($models);

        return $models;
    }

    public function first(): ?Model
    {
        $this->builder->limit(1);
        $model = array_first($this->database->fetchAsObject($this->class));

        if (is_null($model)) {
            return null;
        }

        $map = IdentityMap::getInstance();

        if ($map->isManaged($model)) {
            return $map->find($model::class, $model->getPrimaryKey());
        }

        $this->setExists($model);
        UnitOfWork::getInstance()->registerClean($model);

        return $model;
    }

    //todo: add and() and or() methods
    public function orWhere(string $field, $value, string $expression = '='): self
    {
        $this->builder->orWhere($field, $value, $expression);

        return $this;
    }

    public function andWhere(string $field, $value, string $expression = '='): self
    {
        $this->builder->andWhere($field, $value, $expression);

        return $this;
    }

    public function orWhereIn(string $field, array $value): self
    {
        $this->builder->orWhereIn($field, $value);

        return $this;
    }

    public function andWhereIn(string $field, array $value): self
    {
        $this->builder->andWhereIn($field, $value);

        return $this;
    }

    public function orNative(string $expression): self
    {
        $this->builder->orNative($expression);

        return $this;
    }

    public function andNative(string $expression): self
    {
        $this->builder->andNative($expression);

        return $this;
    }

    private function setExists(Model|ModelCollection $models): void
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        foreach ($models as $model) {
            $reflection = new ReflectionProperty(Model::class, 'exists');
            $reflection->setAccessible(true);
            $reflection->setValue($model, true);
        }
    }
}
