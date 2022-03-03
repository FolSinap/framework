<?php

namespace FW\Kernel\Storage\Cache\Database;

use Carbon\Carbon;
use FW\Kernel\Database\ORM\Models\Model;

class Cache extends Model
{
    public static function getTableName(): string
    {
        return 'cache';
    }

    protected static array $casts = [
        'id' => 'string',
        'key' => 'string',
        'payload' => 'string',
        'expires_at' => Carbon::class,
        'updated_at' => Carbon::class,
    ];

    public static function getColumns(): array
    {
        return ['id', 'payload', 'expires_at', 'updated_at'];
    }
}
