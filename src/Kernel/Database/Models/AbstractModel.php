<?php

namespace Fwt\Framework\Kernel\Database\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;

abstract class AbstractModel
{
    public Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public static function find($id): self
    {
        $database = self::getDatabase();

        return $database->selectClass(static::getTableName(), static::class, ['id' => $id], [$database]);
    }

    public static function all(): array
    {
        $database = self::getDatabase();

        return $database->selectClass(static::getTableName(), static::class, [], [$database]);
    }

    public static function where(array $where): array
    {
        $database = self::getDatabase();

        return $database->selectClass(static::getTableName(), static::class, $where, [$database]);
    }

    public static function getTableName(): string
    {
        $explode = explode('\\', static::class);
        $single = strtolower(array_pop($explode));

        return str_ends_with($single, 'y')
            ? rtrim($single, 'y') . 'ies'
            : $single . 's';
    }

    private static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }
}
