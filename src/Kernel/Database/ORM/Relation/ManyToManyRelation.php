<?php

namespace FW\Kernel\Database\ORM\Relation;

use FW\Kernel\App;
use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\ModelCollection;
use FW\Kernel\Database\ORM\ModelRepository;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Models\AnonymousModel;
use FW\Kernel\Exceptions\ORM\ModelInitializationException;

class ManyToManyRelation extends OneToManyRelation
{
    protected string $pivot;
    protected string $definedBy;

    public function __construct(
        Model $from,
        string $related,
        string $field,
        string $definedBy,
        string $pivot = null,
        ?string $inversedBy = null
    ) {
        parent::__construct($from, $related, $field, $inversedBy);

        $this->pivot = $pivot ?? $this->defaultPivot();
        $this->definedBy = $definedBy;
    }

    public function delete(Model $model): void
    {
        $this->checkClass($model);

        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        $primary = $model->primary();
        $primary = array_pop($primary);

        $database->delete($this->pivot)
            ->where($this->definedBy, $this->getFromPrimary())
            ->andWhere($this->through, $primary);

        $database->execute();
    }

    public function clear(): void
    {
        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        $database->delete($this->pivot)->where($this->definedBy, $this->getFromPrimary());

        $database->execute();
    }

    public function add(Model $model): void
    {
        $this->checkClass($model);

        $model->synchronize();

        $id = $this->from->primary();

        AnonymousModel::setTableName($this->pivot);
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
        AnonymousModel::setTableName($this->pivot);

        $forInsertion = new ModelCollection();
        $id = $this->getFromPrimary();

        /** @var Model $model */
        foreach ($models as $model) {
            $this->checkClass($model);

            if (!$model->exists()) {
                throw ModelInitializationException::nonexistentModel($model);
            }

            $primary = $model->primary();
            $primary = array_pop($primary);

            $forInsertion[] = AnonymousModel::createDry([
                $this->definedBy => $id,
                $this->through => $primary,
            ]);
        }

        $repository = new ModelRepository();

        $repository->insertMany($forInsertion);
    }

    public function get(): ModelCollection
    {
        /** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);

        //todo: use subquery here
        $database->select($this->pivot, [$this->through])->where($this->definedBy, $this->getFromPrimary());
        $ids = $database->fetchAssoc();
        $ids = array_map(function ($value) {
            return $value[$this->through];
        }, $ids);

        if (empty($ids)) {
            return new ModelCollection();
        }

        return $this->related::whereIn($this->getRelatedPrimaryColumn(), $ids)->fetch();
    }

    protected function defaultPivot(): string
    {
        $tables = [$this->from::getTableName(), $this->related::getTableName()];

        sort($tables);

        return implode('_', $tables);
    }
}