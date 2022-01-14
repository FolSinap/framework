<?php

namespace Fwt\Framework\Kernel\Database\ORM\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\ModelCollection;
use Fwt\Framework\Kernel\Database\ORM\Relation;
use Fwt\Framework\Kernel\Database\ORM\Relation\RelationFactory;
use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\ORM\ModelInitializationException;

abstract class AbstractModel
{
    protected const RELATIONS = [];

    protected static array $tableNames;
    protected array $fields = [];
    /** @var Relation[] $relations */
    private array $relations = [];
    private bool $isInitialized = false;
    private ?RelationFactory $relationFactory;

    public function __construct()
    {
        $this->relationFactory = $this->getFactory();
        $this->initRelations();
    }

    public static function find($id): ?self
    {
        $database = self::getDatabase();

        $database->select(static::getTableName())->where(static::getIdColumn(), $id);

        $object = new ModelCollection($database->fetchAsObject(static::class));
        self::setInitializedAll($object);

        return $object->isEmpty() ? null : $object[0];
    }

    public static function all(): ModelCollection
    {
        $database = self::getDatabase();

        $database->select(static::getTableName());

        $models = new ModelCollection($database->fetchAsObject(static::class));

        self::setInitializedAll($models);

        return $models;
    }

    public static function createDry(array $data): self
    {
        $object = new static();

        foreach ($data as $property => $value) {
            $object->$property = $value;
        }

        $object->setInitialized(false);

        return $object;
    }

    public static function create(array $data): self
    {
        $object = static::createDry($data);

        $object->insert();

        return $object;
    }

    public function initialize(): self
    {
        $id = $this->{static::getIdColumn()};

        if (!$id) {
            throw ModelInitializationException::idIsNotSet($this);
        }

        $database = self::getDatabase();

        $database->select(static::getTableName())
            ->where(static::getIdColumn(), $id);

        if (!is_null($database->populateModel($this))) {
            //todo: otherwise throw exception??
            $this->setInitialized();
        }

        $this->initRelations();

        return $this;
    }

    public function delete(): void
    {
        $database = self::getDatabase();
        $id = static::getIdColumn();

        $database->delete(static::getTableName())
            ->where($id, $this->$id);

        $database->execute();
        $this->setInitialized(false);
    }

    public function update(array $data): void
    {
        if (!$this->isInitialized()) {
            throw ModelInitializationException::updatingNotInitializedModel($this);
        }

        $database = self::getDatabase();
        $id = static::getIdColumn();

        $database->update(static::getTableName(), $data)->where($id, $this->$id);

        $database->execute();
    }

    public static function getIdColumn(): string
    {
        //todo: add cases where primary key includes multiple cols

        return 'id';
    }

    public static function where($where): ModelCollection
    {
        $database = self::getDatabase();

        $queryBuilder = $database->getQueryBuilder()->select(static::getTableName());

        if ($where instanceof WhereBuilder) {
            $queryBuilder->whereFromBuilder($where);
        } elseif (is_array($where)) {
            $firstField = array_key_first($where);
            $firstValue = array_shift($where);

            if (is_array($firstValue)) {
                $expression = $firstValue[1];
                $firstValue = $firstValue[0];
            }

            $queryBuilder->where($firstField, $firstValue, $expression ?? '=');

            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    $value = $value[0];
                    $expression = $value[1];
                }

                $queryBuilder->andWhere($field, $value, $expression ?? '=');
            }
        } else {
            throw new IllegalTypeException($where, ['array', WhereBuilder::class]);
        }

        $models = new ModelCollection($database->fetchAsObject(static::class));
        self::setInitializedAll($models);

        return $models;
    }

    public static function getTableName(): string
    {
        if (!isset(static::$tableNames) || !array_key_exists(static::class, static::$tableNames)) {
            $explode = explode('\\', static::class);
            $single = strtolower(array_pop($explode));

            $lastLetter = substr($single, -1);
            $lastTwoLetter = substr($single, -2);

            if (in_array($lastLetter, ['x', 's']) || in_array($lastTwoLetter, ['sh', 'ch'])) {
                static::$tableNames[static::class] = $single . 'es';
            } elseif ($lastLetter === 'y') {
                static::$tableNames[static::class] = rtrim($single, 'y') . 'ies';
            } else {
                static::$tableNames[static::class] = $single . 's';
            }
        }

        return static::$tableNames[static::class];
    }

    public function prepareForExport()
    {
        $this->relations = [];
        $this->relationFactory = null;
    }

    public function insert(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $database = static::getDatabase();
        $data = array_diff_key($this->fields, $this->relations);

        unset($data['isInitialized']);

        //todo: what if multiple cols are primary keys?
        $this->{static::getIdColumn()} = $database->insert($data, static::getTableName());

        $this->setInitialized()->initRelations();
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    public static function __set_state($fields): self
    {
        return static::createDry($fields);
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name]->get();
            $this->$name = $relation;
        }

        return $this->fields[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        if (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name];
            $relatedClass = $relation->getRelated();

            if ($relation instanceof Relation\ToOneRelation) {
                if (is_null($value)) {
                    $this->{$relation->getConnectField()} = $value;
                } else {
                    if (!$value instanceof $relatedClass) {
                        throw new IllegalTypeException($value, [$relatedClass]);
                    }

                    $this->{$relation->getConnectField()} = $value->{$value::getIdColumn()};
                }
            }
        }

        $this->fields[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->fields[$name]);
    }

    protected function setInitialized(bool $isInitialized = true): self
    {
        $this->isInitialized = $isInitialized;

        return $this;
    }

    protected static function setInitializedAll(ModelCollection $models, bool $isInitialized = true): void
    {
        foreach ($models as $model) {
            if (!$model instanceof self) {
                throw new InvalidExtensionException($model, self::class);
            }

            $model->setInitialized($isInitialized);
        }
    }

    protected static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }

    private function initRelations(): self
    {
        foreach (static::RELATIONS as $field => $definition) {
            if (isset($this->$field)) {
                continue;
            }

            $relation = $this->getFactory()->create($this, $definition);

            $this->$field = $relation->getDry();
            $this->relations[$field] = $relation;
        }

        return $this;
    }

    private function getFactory(): RelationFactory
    {
        if (!isset($this->relationFactory)) {
            $this->relationFactory = new RelationFactory();
        }

        return $this->relationFactory;
    }
}