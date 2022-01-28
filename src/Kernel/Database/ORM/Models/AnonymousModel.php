<?php

namespace Fwt\Framework\Kernel\Database\ORM\Models;

class AnonymousModel extends Model
{
    public static array $tableNames;

    public static function setTableName(string $name): void
    {
        self::$tableNames[self::class] = $name;
    }
}
