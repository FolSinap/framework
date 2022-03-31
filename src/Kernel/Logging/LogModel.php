<?php

namespace FW\Kernel\Logging;

use FW\Kernel\Database\ORM\Models\Model;

class LogModel extends Model
{
    public static function getTableName(): string
    {
        return 'logs';
    }

    public static function getColumns(): array
    {
        return ['id', 'channel', 'message', 'level', 'time'];
    }
}
