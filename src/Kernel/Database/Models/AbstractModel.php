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
        /*** @var Database $database */
        $database = App::$app->getContainer()->get(Database::class);
        $explode = explode('\\', static::class);

        return $database->selectClass(strtolower(array_pop($explode)), static::class, [$database]);
    }
}
