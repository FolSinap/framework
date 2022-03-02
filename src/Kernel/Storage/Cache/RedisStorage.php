<?php

namespace FW\Kernel\Storage\Cache;

use FW\Kernel\Database\Redis\Redis;

class RedisStorage
{
    public function __construct(
        protected Redis $connection
    ) {
    }
}
