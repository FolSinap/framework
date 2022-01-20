<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
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

    public function delete(AbstractModel $model): void
    {
        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        $database->delete($this->pivot)
            ->where($this->definedBy, $this->from->primary())
            ->andWhere($this->through, $model->primary());

        $database->execute();
    }

    public function clear(): void
    {
        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        $database->delete($this->pivot)->where($this->definedBy, $this->from->primary());

        $database->execute();
    }

    public function add(AbstractModel $model): void
    {
        $this->checkClass($model);

        $model->synchronize();

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

    public function get(): ModelCollection
    {
        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        //todo: use subquery here
        $database->select($this->pivot, [$this->through])->where($this->definedBy, $this->from->primary());
        $ids = $database->fetchAssoc();
        $ids = array_map(function ($value) {
            return $value[$this->through];
        }, $ids);

        if (empty($ids)) {
            return new ModelCollection();
        }

        return $this->related::whereIn($this->related::getIdColumn(), $ids)->fetch();
    }

    protected function defaultPivot(): string
    {
        $tables = [$this->from::getTableName(), $this->related::getTableName()];

        sort($tables);

        return implode('_', $tables);
    }
}