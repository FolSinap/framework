<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\ModelCollection;
use Fwt\Framework\Kernel\Database\ORM\ModelRepository;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;

class OneToManyRelation extends AbstractRelation
{
    public function __construct(AbstractModel $from, string $related, string $field)
    {
        parent::__construct($from, $related, $field);
    }

    public function add(AbstractModel $model): void
    {
        $this->checkClassAndUpdateForeign($model);

        $model->synchronize();
    }

    public function addMany(ModelCollection $models): void
    {
        $forUpdate = [];
        $forInsert = [];

        /** @var AbstractModel $model */
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
        $repository->updateMany(new ModelCollection($forUpdate), [$this->through => $this->from->primary()]);
    }

    public function delete(AbstractModel $model)
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

    protected function checkClassAndUpdateForeign(AbstractModel $model): void
    {
        $this->checkClass($model);
        $this->updateForeign($model);
    }

    protected function checkClassAndDeleteForeign(AbstractModel $model): void
    {
        $this->checkClass($model);
        $this->deleteForeign($model);
    }

    protected function updateForeign(AbstractModel $model): void
    {
        $model->{$this->through} = $this->from->primary();
    }

    protected function deleteForeign(AbstractModel $model): void
    {
        $model->{$this->through} = null;
    }

    protected function checkClass(AbstractModel $model): void
    {
        if ($this->isRelated($model)) {
            //todo: exception
            throw new \Exception();
        }
    }
}
