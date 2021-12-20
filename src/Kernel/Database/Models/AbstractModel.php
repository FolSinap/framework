<?php

namespace Fwt\Framework\Kernel\Database\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;

abstract class AbstractModel
{
    public static function find($id): ?self
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select()
            ->from(static::getTableName())
            ->where(static::getIdColumn(), $id);

        $object = $database->fetchAsObject(static::class);
        return empty($object) ? null : $object[0];
    }

    public static function all(): array
    {
        $database = self::getDatabase();

        $database->getQueryBuilder()->select()->from(static::getTableName());

        return $database->fetchAsObject(static::class);
    }

    public static function create(array $data): self
    {
        $object = new static();

        foreach ($data as $property => $value) {
            $object->$property = $value;
        }

        $database = self::getDatabase();

        $database->insert($data, $object::getTableName());

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

        return $database->fetchAsObject(static::class);
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

    private static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }
}
