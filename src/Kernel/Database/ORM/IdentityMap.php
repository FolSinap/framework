<?php

namespace FW\Kernel\Database\ORM;

use FW\Kernel\Database\ORM\Models\Model;
use Ds\Map;
use FW\Kernel\Database\ORM\Models\PrimaryKey;

class IdentityMap
{
    private static self $instance;
    private Map $models;

    private function __construct()
    {
        $this->models = new Map();
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function find(string $class, array|PrimaryKey $key): ?Model
    {
        $key = is_array($key) ? $key : $key->getValues();

        if (isset($this->models[$class][$key])) {
            return $this->models[$class][$key];
        }

        return null;
    }

    public function isManaged(Model $model): bool
    {
        if (!$this->models->hasKey($model::class)) {
            return false;
        }

        if ($model->getPrimaryKey()->isUnknown()) {
            return isset($this->models[$model::class]['unknown'][spl_object_id($model)]);
        }

        return isset($this->models[$model::class][$model->getPrimaryKey()->getValues()]);
    }

    public function add(Model $model): void
    {
        if ($this->models->hasKey($model::class)) {
            if ($model->getPrimaryKey()->isUnknown()) {
                $this->models[$model::class]['unknown'][spl_object_id($model)] = $model;
            } else {
                $this->models[$model::class][$model->getPrimaryKey()->getValues()] = $model;
            }

            return;
        }

        $map = new Map();

        if ($model->getPrimaryKey()->isUnknown()) {
            $map->put('unknown', [spl_object_id($model) => $model]);
        } else {
            $map->put($model->getPrimaryKey()->getValues(), $model);
        }

        $this->models[$model::class] = $map;
    }

    public function addMany(array|ModelCollection $models)
    {
        foreach ($models as $model) {
            $this->add($model);
        }
    }

    public function delete(Model $model): void
    {
        if ($this->models->hasKey($model::class)) {
            if ($model->getPrimaryKey()->isUnknown()) {
                unset($this->models[$model::class]['unknown'][spl_object_id($model)]);

                return;
            }

            unset($this->models[$model::class][$model->getPrimaryKey()->getValues()]);
        }
    }

    public function clear(): void
    {
        $this->models->clear();
    }
}
