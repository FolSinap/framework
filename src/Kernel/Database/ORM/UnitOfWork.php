<?php

namespace FW\Kernel\Database\ORM;

use Ds\Map;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Models\PrimaryKey;

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
            if ($model->getPrimaryKey()->isUnknown()) {
                $id = spl_object_id($model);
                $this->new['unknown'] = [$id => $model];
            } else {
                $this->new->put($model->getPrimaryKey()->getValues(), $model);
            }

            $this->deleteFromList($model);
        }
    }

    public function registerDirty(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        $this->addToIdentityMap($models);

        foreach ($models as $model) {
            if ($model->getPrimaryKey()->isUnknown()) {
                $id = spl_object_id($model);
                $this->dirty['unknown'] = [$id => $model];
            } else {
                $this->dirty->put($model->getPrimaryKey()->getValues(), $model);
            }

            $this->deleteFromList($model);
        }
    }

    public function registerClean(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        $this->addToIdentityMap($models);

        foreach ($models as $model) {
            if ($model->getPrimaryKey()->isUnknown()) {
                $id = spl_object_id($model);
                $this->clean['unknown'] = [$id => $model];

                return;
            }

            $this->clean->put($model->getPrimaryKey()->getValues(), $model);

            $this->deleteFromList($model, 'new');
            $this->deleteFromList($model, 'dirty');
            $this->deleteFromList($model, 'deleted');
        }
    }

    public function registerDeleted(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        foreach ($models as $model) {
            if ($model->getPrimaryKey()->isUnknown()) {
                $id = spl_object_id($model);
                $this->deleted['unknown'] = [$id => $model];
            } else {
                $this->deleted->put($model->getPrimaryKey()->getValues(), $model);
            }

            $this->deleteFromList($model);
        }
    }

    public function remove(ModelCollection|Model|array $models)
    {
        $models = $models instanceof Model ? new ModelCollection([$models]) : $models;

        foreach ($models as $model) {
            $this->identityMap->delete($model);
            $this->deleteFromList($model);
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

    protected function addToIdentityMap(ModelCollection|array $models): void
    {
        foreach ($models as $model) {
            if (!$this->identityMap->isManaged($model)) {
                $this->identityMap->add($model);
            }
        }
    }

    protected function deleteFromList(Model $model, string $list = 'clean'): void
    {
        if ($this->{$list}->hasKey('unknown')) {
            unset($this->{$list}['unknown'][spl_object_id($model)]);
        }

        if ($this->{$list}->hasKey($model->getPrimaryKey()->getValues())) {
            $this->{$list}->remove($model->getPrimaryKey()->getValues());
        }
    }

    protected function insertNew(): void
    {
        if (!isset($this->new) || empty($this->new)) {
            return;
        }

        $new = [];

        foreach ($this->new as $model) {
            if ($model instanceof Model) {
                $new[] = $model;
            } elseif (is_array($model)) {
                array_push($new, ...$model);
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

        foreach ($this->dirty as $model) {
            if ($model instanceof Model) {
                $dirty[] = $model;
            } elseif (is_array($model)) {
                array_push($dirty, ...$model);
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

        foreach ($this->deleted as $model) {
            if ($model instanceof Model) {
                $deleted[] = $model;
            } elseif (is_array($model)) {
                array_push($deleted, ...$model);
            }
        }

        $this->deleted->clear();
        (new ModelRepository())->deleteMany($deleted);
    }
}
