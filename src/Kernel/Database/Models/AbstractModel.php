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
            ->where('id', ':id')
            ->setParams(['id' => $id]);

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

//    public static function where(array $where): array
//    {
//        $database = self::getDatabase();
//
//        $database->getQueryBuilder()->select()
//            ->from(static::getTableName())
//            ->where('id', ':id')
//            ->setParams(['id' => $id]);
//
//        return $database->selectClass(static::getTableName(), static::class, $where, [$database]);
//    }

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
