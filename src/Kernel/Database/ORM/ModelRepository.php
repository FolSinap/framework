<?php

namespace FW\Kernel\Database\ORM;

use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\Models\AnonymousModel;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Database\ORM\Models\PrimaryKey;
use FW\Kernel\Database\ORM\Relation\OneToManyRelation;
use FW\Kernel\Database\ORM\Relation\ToOneRelation;
use FW\Kernel\Database\QueryBuilder\Where\Expression;
use FW\Kernel\Exceptions\InvalidExtensionException;
use FW\Kernel\Exceptions\ORM\ModelInitializationException;

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

    public function allByClass(string $class, array $relations = []): ModelCollection
    {
        $this->checkClass($class);

        if (!empty($relations)) {
            return $this->loadRelations($class, $relations);
        }

        $this->database->select($class::getTableName());

        return new ModelCollection($this->database->fetchAsObject($class));
    }

    protected function loadRelations(string $class, array $relations): ModelCollection
    {
        $joins = [];
        $cols = $this->generateAliases($class);

        foreach ($relations as $relation) {
            /** @var Model $class */
            $relation = $class::RELATIONS[$relation];
            $type = $relation['type'] ?? Relation\Relation::TO_ONE;

            switch ($type) {
                case Relation\Relation::TO_ONE:
                    $related = $relation['class'];
                    $ids = $related::getIdColumns();
                    $id = array_shift($ids);
                    $join['table'] = $related::getTableName();
                    $join['on'] = $class::getTableName() . '.' . $relation['field'] . ' = ' . $related::getTableName() . '.' . $id;

                    $cols = array_merge($cols, $this->generateAliases($related));
                    $joins[] = $join;

                    break;
                case Relation\Relation::ONE_TO_MANY:
                    $related = $relation['class'];
                    $ids = $class::getIdColumns();
                    $id = array_shift($ids);
                    $join['table'] = $related::getTableName();
                    $join['on'] = $class::getTableName() . '.' . $id . ' = ' . $related::getTableName() . '.' . $relation['field'];

                    $cols = array_merge($cols, $this->generateAliases($related));
                    $joins[] = $join;

                    break;
                case Relation\Relation::MANY_TO_MANY:
                    //todo: generate default pivot
                    $pivot = $relation['pivot'] ?? 'def';

                    $related = $relation['class'];
                    $field = $relation['field'];
                    $definedBy = $relation['defined_by'];

                    $ids = $class::getIdColumns();
                    $id = array_shift($ids);

                    $join['table'] = $pivot;
                    $join['on'] = $class::getTableName() . '.' . $id . ' = ' . $pivot . '.' . $definedBy;

                    $joins[] = $join;

                    $ids = $related::getIdColumns();
                    $id = array_shift($ids);

                    $join['table'] = $related::getTableName();
                    $join['on'] = $related::getTableName() . '.' . $id . ' = ' . $pivot . '.' . $field;

                    $joins[] = $join;

                    $cols = array_merge($cols, $this->generateAliases($related));
            }
        }

        $select = $this->database->select($class::getTableName(), array_column($cols, 'query'));

        foreach ($joins as $join) {
            $select->leftJoin($join['table'], $join['on']);
        }

        $normalized = $this->combineModels($this->database->fetchAssoc(), $cols);

        $fetched = [];

        foreach ($normalized as $result) {
            $main = $result[$class];

            if (array_key_exists(implode('', $main->primary()), $fetched)) {
                $main = $fetched[implode('', $main->primary())];
            } else {
                $fetched[implode('', $main->primary())] = $main;
            }

            foreach ($relations as $relation) {
                $relationObject = $main->getRelation($relation);
                $related = $result[$relationObject->getRelated()];

                switch (true) {
                    case $relationObject instanceof ToOneRelation:
                        $main->__set($relation, $related);

                        break;
                    default:
                        if ($related instanceof Model) {
                            $main->getLazy($relation)->add($related);
                        }

                        break;
                }
            }
        }

        return new ModelCollection($fetched);
    }

    protected function combineModels(array $fetched, array $properties): array
    {
        $data = [];

        foreach ($fetched as $result) {
            foreach ($result as $alias => $value) {
                foreach ($properties as $property) {
                    if ($alias === $property['alias']) {
                        $model[$property['model']][$property['column']] = $value;
                    }
                }
            }

            $data[] = $model;
        }

        foreach ($data as $key => $entry) {
            foreach ($entry as $class => $properties) {
                $properties = array_filter($properties, function ($property) {
                    return !is_null($property);
                });

                if (empty($properties)) {
                    $data[$key][$class] = null;
                } else {
                    $data[$key][$class] = $class::createDry($properties);
                }
            }
        }

        return $data;
    }

    protected function generateAliases(string $model, string ...$models): array
    {
        $models = func_get_args();
        $aliases = [];

        foreach ($models as $model) {
            $this->checkClass($model);
            $table = $model::getTableName();

            foreach ($model::getColumns() as $column) {
                $alias['table'] = $table;
                $alias['column'] = $column;
                $alias['query'] = "{$table}.{$column} as {$table}_{$column}";
                $alias['alias'] = "{$table}_{$column}";
                $alias['model'] = $model;

                $aliases[] = $alias;
            }
        }

        return $aliases;
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
