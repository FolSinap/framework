<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\ModelCollection;
use Fwt\Framework\Kernel\Database\ORM\ModelRepository;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Models\AnonymousModel;

class ManyToManyRelation extends OneToManyRelation
{
    protected string $pivot;
    protected string $definedBy;

    public function __construct(AbstractModel $from, string $related, string $field, string $definedBy, string $pivot = null)
    {
        parent::__construct($from, $related, $field);

        $this->pivot = $pivot ?? $this->defaultPivot();
        $this->definedBy = $definedBy;
    }

    public function add(AbstractModel $model): void
    {
        $this->checkClass($model);

        if ($model->isInitialized()) {
            $model->toDb();
        }

        $id = $this->from->primary();

        AnonymousModel::$tableNames[AnonymousModel::class] = $this->pivot;
        $pivot = AnonymousModel::where($this->definedBy, $id)
            ->andWhere($this->through, $model->primary())
            ->fetch();

        if ($pivot->isEmpty()) {
            AnonymousModel::create([
                $this->definedBy => $id,
                $this->through => $model->primary(),
            ]);
        }
    }

    public function addMany(ModelCollection $models): void
    {
        $forInsertion = new ModelCollection();
        $id = $this->from->primary();

        foreach ($models as $model) {
            if (!$model->isInitialized()) {
                //todo: change exception
                throw new \Exception('Model must be initialized');
            }

            $forInsertion[] = AnonymousModel::createDry([
                $this->definedBy => $id,
                $this->through => $model->primary(),
            ]);
        }

        /** @var ModelRepository $repository */
        $repository = ModelRepository::getInstance();

        $repository->insertMany($forInsertion);
    }

    public function getDry(): ModelCollection
    {
        if (!isset($this->dry)) {
            AnonymousModel::$tableNames[AnonymousModel::class] = $this->pivot;

            $id = $this->from->primary();

            if (is_null($id)) {
                $this->dry = new ModelCollection();

                return $this->dry;
            }

            $pivots = AnonymousModel::where($this->definedBy, $id)->fetch();

            foreach ($pivots as $key => $pivot) {
                $pivots[$key] = $this->related::createDry([$this->related::getIdColumn() => $pivot->{$this->through}]);
            }

            $this->dry = $pivots;
        }

        return $this->dry;
    }

    protected function defaultPivot(): string
    {
        $tables = [$this->from::getTableName(), $this->related::getTableName()];

        sort($tables);

        return implode('_', $tables);
    }
}