<?php

namespace Fwt\Framework\Kernel\Database\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;

abstract class AbstractModel
{
    protected const RELATIONS = [];

    protected array $fields = [];
    private array $relations = [];
    private bool $isInitialized = false;

    public function __construct()
    {
        $this->initRelations();
    }

    public static function __set_state($fields): self
    {
        return static::createDry($fields);
    }

    public function initialize(): self
    {
        $id = $this->{static::getIdColumn()};

        if (!$id) {
            //todo: change exception
            throw new \Exception('Cannot initialize model when id is not set.');
        }

        $database = self::getDatabase();

        $database->getQueryBuilder()->select(static::getTableName())
            ->where(static::getIdColumn(), $id);

        if (!is_null($database->populateModel($this))) {
            //todo: otherwise throw exception??
            $this->setInitialized();
        }

        return $this;
    }

    private function initRelations(): void
    {
        foreach (static::RELATIONS as $field => $relation) {
            $relation = new Relation($this, ...$relation);

            $this->$field = $relation->getDry();
            $this->relations[$field] = $relation;
        }
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

    public static function find($id): ?self
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select(static::getTableName())
            ->where(static::getIdColumn(), $id);

        $object = $database->fetchAsObject(static::class);
        self::setInitializedAll($object);

        return empty($object) ? null : $object[0];
    }

    public static function all(): array
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select(static::getTableName());

        $models = $database->fetchAsObject(static::class);

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
        $object = new static();

        foreach ($data as $property => $value) {
            $object->$property = $value;
        }

        $object->insert();

        return $object;
    }

    public function delete()
    {
        $database = self::getDatabase();
        $id = static::getIdColumn();

        $database->getQueryBuilder()
            ->delete()->from(static::getTableName())
            ->where($id, $this->$id);

        $database->selfExecute();
        $this->setInitialized(false);
    }

    public function update(array $data): void
    {
        if (!$this->isInitialized()) {
            //todo: change exception
            throw new \Exception('Cannot update not initialized model');
        }

        $database = self::getDatabase();
        $id = static::getIdColumn();

        $database->getQueryBuilder()
            ->update(static::getTableName())
            ->set($data)
            ->where($id, $this->$id);

        $database->selfExecute();
    }

    public static function getIdColumn(): string
    {
        //todo: add cases where primary key includes multiple cols

        return 'id';
    }

    public static function where(array $where): array
    {
        $database = self::getDatabase();

        $queryBuilder = $database->getQueryBuilder()->select(static::getTableName());

        $firstField = array_key_first($where);
        $firstValue = array_shift($where);

        if (is_array($firstValue)) {
            $firstValue = $firstValue[0];
            $expression = $firstValue[1];
        }

        $queryBuilder->where($firstField, $firstValue, $expression ?? '=');

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $value = $value[0];
                $expression = $value[1];
            }

            $queryBuilder->andWhere($field, $value, $expression ?? '=');
        }

        $models = $database->fetchAsObject(static::class);
        self::setInitializedAll($models);

        return $models;
    }

    public static function getTableName(): string
    {
        $explode = explode('\\', static::class);
        $single = strtolower(array_pop($explode));

        $lastLetter = substr($single, -1);
        $lastTwoLetter = substr($single, -2);

        if (in_array($lastLetter, ['x', 's']) || in_array($lastTwoLetter, ['sh', 'ch'])) {
            return $single . 'es';
        } elseif ($lastLetter === 'y') {
            return rtrim($single, 'y') . 'ies';
        } else {
            return $single . 's';
        }
    }

    public function insert(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $database = static::getDatabase();
        $data = array_diff_key($this->fields, $this->relations);

        unset($data['isInitialized']);

        $database->insert($data, static::getTableName());
        $this->setInitialized();
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    protected static function setInitializedAll(array $models, bool $isInitialized = true): void
    {
        foreach ($models as $model) {
            if (!$model instanceof self) {
                throw new InvalidExtensionException($model, self::class);
            }

            $model->setInitialized($isInitialized);
        }
    }

    protected function setInitialized(bool $isInitialized = true): void
    {
        $this->isInitialized = $isInitialized;
    }

    protected static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }
}
