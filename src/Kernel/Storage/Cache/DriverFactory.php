<?php

namespace FW\Kernel\Storage\Cache;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Memcached;
use FW\Kernel\Database\Redis;
use FW\Kernel\ObjectResolver;
use FW\Kernel\Storage\Cache\Drivers\RedisDriver;
use FW\Kernel\Storage\Cache\Drivers\DatabaseDriver;
use FW\Kernel\Storage\Cache\Drivers\FilesDriver;
use FW\Kernel\Storage\Cache\Drivers\MemcachedDriver;
use FW\Kernel\Storage\Cache\Drivers\ArrayDriver;

class DriverFactory
{
    public function __construct(
        protected FileConfig $config
    ) {
    }

    public function create(string $driver = null): ICacheDriver
    {
        return match ($driver ?? $this->config->get('driver', false)) {
            'redis' => new RedisDriver(
                new Redis($this->config->get('redis', false))
            ),

            'database' => new DatabaseDriver(),

            'files' => new FilesDriver(
                project_dir() . '/' . $this->config->get('files.dir')
            ),

            'memcached' => new MemcachedDriver(
                new Memcached($this->config->get('memcached.servers', false))
            ),

            default => new ArrayDriver(),
        };
    }
}
