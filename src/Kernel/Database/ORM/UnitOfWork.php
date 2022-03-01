<?php

namespace FW\Kernel\Database\ORM;

use Ds\Map;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Models\PrimaryKey;
use FW\Kernel\Exceptions\IllegalValueException;

class UnitOfWork
{
    private static self $instance;
    protected Map $new;
    protected Map $dirty;
    protected Map $clean;
    protected Map $deleted;
    protected IdentityMap $identityMap;

    private function __construct()
    {
        $this->new = new Map();
        $this->dirty = new Map();
        $this->clean = new Map();
        $this->deleted = new Map();
        $this->identityMap = IdentityMap::getInstance();
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
        return $this->identityMap->find($class, $key);
    }

    public function registerNew(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        $this->addToIdentityMap($models);

        foreach ($models as $model) {
            $this->add($model, 'new');
            $this->deleteFromList($model, 'clean');
        }
    }

    public function registerDirty(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        $this->addToIdentityMap($models);

        foreach ($models as $model) {
            $this->add($model, 'dirty');
            $this->deleteFromList($model, 'clean');
        }
    }

    public function registerClean(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        $this->addToIdentityMap($models);

        foreach ($models as $model) {
            $this->add($model, 'clean');
            $this->deleteFromList($model, 'new');
            $this->deleteFromList($model, 'dirty');
            $this->deleteFromList($model, 'deleted');
        }
    }

    public function registerDeleted(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        foreach ($models as $model) {
            $this->add($model, 'deleted');
            $this->deleteFromList($model, 'clean');
        }
    }

    public function remove(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        foreach ($models as $model) {
            $this->identityMap->delete($model);
            $this->deleteFromList($model, 'clean');
            $this->deleteFromList($model, 'new');
            $this->deleteFromList($model, 'dirty');
            $this->deleteFromList($model, 'deleted');
        }
    }

    public function commit()
    {
        $this->insertNew();
        $this->updateDirty();
        $this->commitDeleted();
    }

    protected function add(Model $model, string $listName): void
    {
        IllegalValueException::checkValue($listName, ['new', 'dirty', 'clean', 'deleted']);

        if ($this->{$listName}->hasKey($model::class)) {
            $map = $this->{$listName}[$model::class];
        } else {
            $map = new Map();
        }

        if ($model->getPrimaryKey()->isUnknown()) {
            $id = spl_object_id($model);

            if ($map->hasKey('unknown')) {
                $map['unknown'][$id] = $model;
            } else {
                $map['unknown'] = [$id => $model];
            }
        } else {
            $map[$model->getPrimaryKey()->getValues()] = $model;
        }

        $this->{$listName}[$model::class] = $map;
    }

    protected function addToIdentityMap(ModelCollection|array $models): void
    {
        foreach ($models as $model) {
            if (!$this->identityMap->isManaged($model)) {
                $this->identityMap->add($model);
            }
        }
    }

    protected function deleteFromList(Model $model, string $listName): void
    {
        IllegalValueException::checkValue($listName, ['new', 'dirty', 'clean', 'deleted']);

        if (!$this->{$listName}->hasKey($model::class)) {
            return;
        }

        $map = $this->{$listName}->get($model::class);

        if ($map->hasKey('unknown')) {
            unset($map['unknown'][spl_object_id($model)]);
        }

        if ($map->hasKey($model->getPrimaryKey()->getValues())) {
            $map->remove($model->getPrimaryKey()->getValues());
        }

        if ($map->isEmpty()) {
            $this->{$listName}->remove($model::class);

            return;
        }

        $this->{$listName}[$model::class] = $map;
    }

    protected function insertNew(): void
    {
        if (!isset($this->new) || empty($this->new)) {
            return;
        }

        $new = [];

        foreach ($this->new as $classMap) {
            foreach ($classMap as $model) {
                if ($model instanceof Model) {
                    $new[] = $model;
                } elseif (is_array($model)) {
                    array_push($new, ...$model);
                }
            }
        }

        (new ModelRepository())->insertMany($new);

        foreach ($new as $model) {
            $this->registerClean($model);
        }

        $this->new->clear();
    }

    protected function updateDirty(): void
    {
        if (!isset($this->dirty) || empty($this->dirty)) {
            return;
        }

        $dirty = [];

        foreach ($this->dirty as $classMap) {
            foreach ($classMap as $model) {
                if ($model instanceof Model) {
                    $dirty[] = $model;
                } elseif (is_array($model)) {
                    array_push($dirty, ...$model);
                }
            }
        }

        foreach ($dirty as $model) {
            (new ModelRepository())->update($model);
            $this->registerClean($model);
        }

        $this->dirty->clear();
    }

    protected function commitDeleted(): void
    {
        if (!isset($this->deleted) || empty($this->deleted)) {
            return;
        }

        $deleted = [];

        foreach ($this->deleted as $classMap) {
            foreach ($classMap as $model) {
                if ($model instanceof Model) {
                    $deleted[] = $model;
                } elseif (is_array($model)) {
                    array_push($deleted, ...$model);
                }
            }
        }

        $this->deleted->clear();
        (new ModelRepository())->deleteMany($deleted);
    }
}
