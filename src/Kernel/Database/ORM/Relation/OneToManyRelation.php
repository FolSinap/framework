<?php

namespace FW\Kernel\Database\ORM\Relation;

use FW\Kernel\Database\ORM\ModelCollection;
use FW\Kernel\Database\ORM\ModelRepository;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Exceptions\NotSupportedException;
use FW\Kernel\Exceptions\ORM\UndefinedRelationException;

class OneToManyRelation extends Relation
{
    public function __construct(Model $from, string $related, string $field, ?string $inversedBy = null)
    {
        parent::__construct($from, $related, $field, $inversedBy);

        if ($from::hasCompositeKey()) {
            throw new NotSupportedException('to-many relation types don\'t support composite keys.');
        }
    }

    public function add(Model $model): void
    {
        $this->checkClassAndUpdateForeign($model);

        $model->synchronize();
    }

    public function addMany(ModelCollection $models): void
    {
        $forUpdate = [];
        $forInsert = [];

        /** @var Model $model */
        foreach ($models as $model) {
            $this->checkClass($model);

            if ($model->exists()) {
                $forUpdate[] = $model;
            } else {
                $this->updateForeign($model);
                $forInsert[] = $model;
            }
        }

        $repository = new ModelRepository();

        $repository->insertMany(new ModelCollection($forInsert));
        $repository->updateMany(new ModelCollection($forUpdate), [$this->through => $this->getFromPrimary()]);
    }

    public function delete(Model $model)
    {
        $this->checkClassAndDeleteForeign($model);

        $model->update();
    }

    public function clear(): void
    {
        $relations = $this->get();

        $repository = new ModelRepository();

        $repository->updateMany($relations, [$this->through => null]);
    }

    public function get(): ModelCollection
    {
        $id = $this->from->primary();

        if (is_null($id)) {
            return new ModelCollection();
        }

        return $this->related::where($this->through, $id)->fetch();
    }

    public function getDry(): ModelCollection
    {
        if (!isset($this->dry)) {
            $this->dry = new ModelCollection();
        }

        return $this->dry;
    }

    protected function checkClassAndUpdateForeign(Model $model): void
    {
        $this->checkClass($model);
        $this->updateForeign($model);
    }

    protected function checkClassAndDeleteForeign(Model $model): void
    {
        $this->checkClass($model);
        $this->deleteForeign($model);
    }

    protected function updateForeign(Model $model): void
    {
        $model->{$this->through} = $this->from->primary();
    }

    protected function deleteForeign(Model $model): void
    {
        $model->{$this->through} = null;
    }

    protected function checkClass(Model $model): void
    {
        if ($this->isRelated($model)) {
            throw new UndefinedRelationException($this->from, $model);
        }
    }

    protected function getFromPrimary()
    {
        $primary = $this->from->primary();

        return array_pop($primary);
    }
}
