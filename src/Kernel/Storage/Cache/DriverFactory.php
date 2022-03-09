<?php

namespace FW\Kernel\Storage\Cache;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\ObjectResolver;
use FW\Kernel\Storage\Cache\Redis\CacheItemPool as RedisDriver;
use FW\Kernel\Storage\Cache\Database\CacheItemPool as DatabaseDriver;
use FW\Kernel\Storage\Cache\Files\CacheItemPool as FilesDriver;
use FW\Kernel\Storage\Cache\Memcached\CacheItemPool as MemcachedDriver;
use FW\Kernel\Storage\Cache\Array\CacheItemPool as ArrayDriver;

class DriverFactory
{
    protected ObjectResolver $resolver;

    public function __construct(
        protected FileConfig $config
    ) {
        $this->resolver = container(ObjectResolver::class);
    }

    public function create(string $driver = null): ICacheDriver
    {
        return match ($driver ?? $this->config->get('driver', false)) {
            'redis' => $this->resolver->resolve(RedisDriver::class),
            'database' => $this->resolver->resolve(DatabaseDriver::class),
            'files' => $this->resolver->resolve(FilesDriver::class),
            'memcached' => $this->resolver->resolve(MemcachedDriver::class),
            default => $this->resolver->resolve(ArrayDriver::class),
        };
    }
}
