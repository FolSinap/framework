<?php

namespace FW\Kernel\Storage\Cache;

use Carbon\Carbon;
use FW\Kernel\Database\ORM\Models\Model;

class CacheModel extends Model
{
    public static function getTableName(): string
    {
        return 'cache';
    }

    protected static array $casts = [
        'id' => 'string',
        'payload' => 'string',
        'expires_at' => Carbon::class,
        'updated_at' => Carbon::class,
    ];

    public static function getColumns(): array
    {
        return ['id', 'payload', 'expires_at', 'updated_at'];
    }
}
