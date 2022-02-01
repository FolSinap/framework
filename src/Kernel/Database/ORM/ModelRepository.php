<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Database\ORM\Models\PrimaryKey;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;

class ModelRepository
{
    protected Database $database;

    public function __construct()
    {
        $this->database = container(Database::class);
    }

    public function getTableScheme(string $class): array
    {
        $this->checkClass($class);

        return $this->database->describe($class::getTableName());
    }

    public function deleteMany(ModelCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $data = [];

        foreach ($models as $model) {
            if (!$model->exists()) {
                continue;
            }

            foreach ($model->primary() as $field => $value) {
                $data[get_class($model)][$field][] = $value;
            }
        }

        foreach (array_keys($data) as $class) {
            $this->database->delete($class::getTableName())->AndWhereInAll($data[$class]);
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
            foreach ($model->primary() as $field => $value) {
                $data[get_class($model)][$field][] = $value;
            }
        }

        foreach (array_keys($data) as $class) {
            $this->database->update($class::getTableName(), $values)->AndWhereInAll($data[$class]);
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

    public function delete(Model $model): void
    {
        $id = $model->primary();

        $this->database->delete($model::getTableName())->andWhereAll($id);

        $this->database->execute();
    }

    public function update(Model $model, array $data = []): void
    {
        if (!$model->exists()) {
            throw ModelInitializationException::nonexistentModel($model);
        }

        $idCols = $model::getIdColumns();
        $idVals = $model->primary();

        if (empty($data)) {
            if (!$model->isChanged()) {
                return;
            }

            $data = $model->getForInsertion();
        } elseif (!empty($model::getColumns())) {
            $data = array_intersect_key($data, array_flip($model::getColumns()));
        }

        $this->database->update($model::getTableName(), $data)->AndWhereAll(array_combine($idCols, $idVals));

        $this->database->execute();
    }

    public function allByClass(string $class): ModelCollection
    {
        $this->checkClass($class);

        $this->database->select($class::getTableName());

        return new ModelCollection($this->database->fetchAsObject($class));
    }

    public function find(string $class, PrimaryKey $id): ?Model
    {
        $this->checkClass($class);

        $this->database->select($class::getTableName())->andWhereAll($id->getValues());

        $object = new ModelCollection($this->database->fetchAsObject($class));

        return $object->isEmpty() ? null : $object[0];
    }

    public function insert(Model $model): void
    {
        if ($model->exists()) {
            return;
        }

        $data = $model->getForInsertion();
        $id = $this->database->insert($data, $model::getTableName());

        if (!$model::hasCompositeKey()) {
            $model->setPrimary($id);
        }
    }

    protected function checkClass(string $class): void
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new InvalidExtensionException($class, Model::class);
        }
    }

    protected function populateModel(Model $model): ?Model
    {
        return $this->database->populateObject($model);
    }
}
