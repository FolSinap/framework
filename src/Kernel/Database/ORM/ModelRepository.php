<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Container;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;

class ModelRepository extends Container
{
    protected Database $database;

    public function where(string $class, string $field, string $value, string $expression = '='): WhereBuilderFacade
    {
        $this->checkClass($class);

        //todo: check in container first
        $database = $this->getDatabase();
        $select = $database->select($class::getTableName())->where($field, $value, $expression);

        return new WhereBuilderFacade($database, $select, $class);
    }

    public function delete(AbstractModel $model): void
    {
        $database = $this->getDatabase();
        $id = $model::getIdColumn();

        $database->delete($model::getTableName())->where($id, $model->$id);

        $database->execute();
    }

    public function update(AbstractModel $model, array $data): void
    {
        if (!$model->isInitialized()) {
            throw ModelInitializationException::updatingNotInitializedModel($model);
        }

        $database = $this->getDatabase();
        $id = $model::getIdColumn();

        $database->update($model::getTableName(), $data)->where($id, $model->$id);

        $database->execute();
    }

    public function dry(string $class, array $data = []): AbstractModel
    {
        $this->checkClass($class);
        $needsToSave = true;

        if (array_key_exists($idCol = $class::getIdColumn(), $data)) {
            if ($model = $this->getByIdIfExists($class, $data[$idCol])) {
                $needsToSave = false;

                unset($data[$idCol]);
            }
        }

        $model = $model ?? new $class();

        foreach ($data as $property => $value) {
            $model->$property = $value;
        }

        if ($needsToSave) {
            $this->save($model);
        }

        return $model;
    }

    public function allByClass(string $class): ModelCollection
    {
        $this->checkClass($class);

        $database = $this->getDatabase();
        $database->select($class::getTableName());

        $models = new ModelCollection($database->fetchAsObject($class));

        $this->saveMany($models);

        return $models;
    }

    public function initializedById(string $class, $id): ?AbstractModel
    {
        $this->checkClass($class);

        $model = $this->getById($class, $id);

        if (!$model->isInitialized()) {
            $model = $this->initialize($model);
        }

        return $model;
    }

    public function getById(string $class, $id): ?AbstractModel
    {
        $this->checkClass($class);

        if ($model = $this->getByIdIfExists($class, $id)) {
            return $model;
        }

        $model = $this->fromDbById($class, $id);
        $this->data[$class][$id] = $model;

        return $model;
    }

    public function getByIdIfExists(string $class, $id): ?AbstractModel
    {
        $this->checkClass($class);

        if (isset($this->data[$class][$id])) {
            return $this->data[$class][$id];
        }

        return null;
    }

    public function initialize(AbstractModel $model): ?AbstractModel
    {
        $id = $model->{$model::getIdColumn()};

        if (!$id) {
            throw ModelInitializationException::idIsNotSet($model);
        }

        $this->getDatabase()->select($model::getTableName())->where($model::getIdColumn(), $id);

        return $this->populateModel($model);
    }

    public function insert(AbstractModel $model): void
    {
        if ($model->isInitialized()) {
            return;
        }

        $database = $this->getDatabase();
        $data = $model->getForInsertion();
        $id = $database->insert($data, $model::getTableName());

        //todo: what if multiple cols are primary keys?
        $model->{$model::getIdColumn()} = $id;

        $this->save($model);
    }

    public function saveMany(ModelCollection $models): void
    {
        /** @var AbstractModel $model */
        foreach ($models as $model) {
            $this->save($model);
        }
    }

    public function save(AbstractModel $model): void
    {
        if ($id = $model->{$model::getIdColumn()}) {
            $this->data[get_class($model)][$id] = $model;
        }
    }

    protected function fromDbById(string $class, $id): ?AbstractModel
    {
        $this->checkClass($class);

        $database = $this->getDatabase();

        /** @var AbstractModel $class */
        $database->select($class::getTableName())->where($class::getIdColumn(), $id);

        $object = new ModelCollection($database->fetchAsObject($class));

        return $object->isEmpty() ? null : $object[0];
    }

    protected function checkClass(string $class): void
    {
        if (!is_subclass_of($class, AbstractModel::class)) {
            throw new InvalidExtensionException($class, AbstractModel::class);
        }
    }

    protected function getDatabase(): Database
    {
        if (!isset($this->database)) {
            $this->database = App::$app->getContainer()->get(Database::class);
        }

        return $this->database;
    }

    protected function populateModel(AbstractModel $model): ?AbstractModel
    {
        return $this->getDatabase()->populateObject($model);
    }
}
