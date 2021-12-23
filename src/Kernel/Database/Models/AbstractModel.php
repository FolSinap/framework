<?php

namespace Fwt\Framework\Kernel\Database\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;

abstract class AbstractModel
{
    private bool $isInitialized = false;

    public static function find($id): ?self
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select()
            ->from(static::getTableName())
            ->where(static::getIdColumn(), $id);

        $object = $database->fetchAsObject(static::class);
        self::initializeAll($object);

        return empty($object) ? null : $object[0];
    }

    public static function all(): array
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select()->from(static::getTableName());

        $models = $database->fetchAsObject(static::class);

        self::initializeAll($models);

        return $models;
    }

    public static function createDry(array $data): self
    {
        $object = new static();

        foreach ($data as $property => $value) {
            $object->$property = $value;
        }

        $object->initialize(false);

        return $object;
    }

    public static function create(array $data): self
    {
        $object = new static();

        foreach ($data as $property => $value) {
            $object->$property = $value;
        }

        $database = self::getDatabase();

        $database->insert($data, $object::getTableName());
        $object->initialize();

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
        $this->initialize(false);
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

        $queryBuilder = $database->getQueryBuilder()->select()
            ->from(static::getTableName());

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
        self::initializeAll($models);

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

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    protected static function initializeAll(array $models, bool $isInitialized = true): void
    {
        foreach ($models as $model) {
            if (!$model instanceof self) {
                //todo: create new exception for this
                throw new \InvalidArgumentException('Value must be of type Model');
            }

            $model->initialize($isInitialized);
        }
    }

    protected function initialize(bool $isInitialized = true): void
    {
        $this->isInitialized = $isInitialized;
    }

    protected static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }
}
