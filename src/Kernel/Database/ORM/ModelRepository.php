<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;

class ModelRepository
{
    protected Database $database;

    public function __construct()
    {
        $this->database = App::$app->getContainer()->get(Database::class);
    }

    public function deleteMany(ModelCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $data = [];

        foreach ($models as $model) {
            if (!$model->isInitialized()) {
                continue;
            }

            $data[get_class($model)][] = $model->primary();
        }

        foreach (array_keys($data) as $class) {
            $this->database->delete($class::getTableName())->whereIn($class::getIdColumn(), $data[$class]);
        }

        $this->database->execute();
    }

    public function insertMany(ModelCollection $models): void
    {
        $data = [];

        foreach ($models as $model) {
            $data[get_class($model)][] = $model->getForInsertion();
        }

        foreach (array_keys($data) as $class) {
            $this->database->insertMany($data[$class], $class::getTableName());
        }
    }

    public function updateMany(ModelCollection $models, array $values)
    {
        $data = [];

        foreach ($models as $model) {
            $data[get_class($model)][] = $model->{$model::getIdColumn()};
        }

        foreach (array_keys($data) as $class) {
            $this->database->update($class::getTableName(), $values)->whereIn($class::getIdColumn(), $data[$class]);
        }

        $this->database->execute();
    }

    public function where(string $class, string $field, string $value, string $expression = '='): WhereBuilderFacade
    {
        $this->checkClass($class);

        $select = $this->database->select($class::getTableName())->where($field, $value, $expression);

        return new WhereBuilderFacade($this->database, $select, $class);
    }

    public function whereIn(string $class, string $field, array $values): WhereBuilderFacade
    {
        $this->checkClass($class);

        $select = $this->database->select($class::getTableName())->whereIn($field, $values);

        return new WhereBuilderFacade($this->database, $select, $class);
    }

    public function delete(AbstractModel $model): void
    {
        $id = $model::getIdColumn();

        $this->database->delete($model::getTableName())->where($id, $model->$id);

        $this->database->execute();
    }

    public function update(AbstractModel $model, array $data = []): void
    {
        if (!$model->exists()) {
            //todo: change exception, updatingNotInitializedModel -> updatingNotExistingModel
            throw ModelInitializationException::updatingNotInitializedModel($model);
        }

        $id = $model::getIdColumn();

        if (empty($data)) {
            if (!$model->isChanged()) {
                return;
            }

            $data = $model->getForInsertion();
        }

        $this->database->update($model::getTableName(), $data)->where($id, $model->$id);

        $this->database->execute();
    }

    public function allByClass(string $class): ModelCollection
    {
        $this->checkClass($class);

        $this->database->select($class::getTableName());

        return new ModelCollection($this->database->fetchAsObject($class));
    }

    public function find(string $class, $id): ?AbstractModel
    {
        $this->checkClass($class);

        $this->database->select($class::getTableName())->where($class::getIdColumn(), $id);

        $object = new ModelCollection($this->database->fetchAsObject($class));

        return $object->isEmpty() ? null : $object[0];
    }

    public function insert(AbstractModel $model): void
    {
        if ($model->exists()) {
            return;
        }

        $data = $model->getForInsertion();
        $id = $this->database->insert($data, $model::getTableName());

        //todo: what if multiple cols are primary keys?
        $model->{$model::getIdColumn()} = $id;
    }

    protected function checkClass(string $class): void
    {
        if (!is_subclass_of($class, AbstractModel::class)) {
            throw new InvalidExtensionException($class, AbstractModel::class);
        }
    }

    protected function populateModel(AbstractModel $model): ?AbstractModel
    {
        return $this->database->populateObject($model);
    }
}
