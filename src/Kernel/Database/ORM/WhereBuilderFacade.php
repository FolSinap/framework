<?php

namespace FW\Kernel\Database\ORM;

use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\QueryBuilder\Data\SelectBuilder;
use FW\Kernel\Exceptions\InvalidExtensionException;
use ReflectionProperty;

class WhereBuilderFacade
{
    protected SelectBuilder $builder;
    protected Database $database;
    protected string $class;

    public function __construct(Database $database, SelectBuilder $builder, string $class)
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new InvalidExtensionException($class, Model::class);
        }

        $this->class = $class;
        $this->database = $database;
        $this->builder = $builder;
    }

    public function fetch(): ModelCollection
    {
        $models = new ModelCollection($this->database->fetchAsObject($this->class));

        $this->setExists($models);

        return $models;
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

    private function setExists(ModelCollection $models): void
    {
        foreach ($models as $model) {
            $reflection = new ReflectionProperty(Model::class, 'exists');
            $reflection->setAccessible(true);
            $reflection->setValue($model, true);
        }
    }
}
