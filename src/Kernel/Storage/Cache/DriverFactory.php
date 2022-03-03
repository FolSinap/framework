<?php

namespace FW\Kernel\Storage\Cache;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\ObjectResolver;
use FW\Kernel\Storage\Cache\Redis\CacheItemPool as RedisDriver;
use FW\Kernel\Storage\Cache\Database\CacheItemPool as DatabaseDriver;
use FW\Kernel\Storage\Cache\Files\CacheItemPool as FilesDriver;

class DriverFactory
{
    protected ObjectResolver $resolver;

    public function __construct(
        protected FileConfig $config
    ) {
        $this->resolver = container(ObjectResolver::class);
    }

    public function create(): ICacheDriver
    {
        return match ($this->config->get('driver')) {
            'redis' => $this->resolver->resolve(RedisDriver::class),
            'database' => $this->resolver->resolve(DatabaseDriver::class),
            'files' => $this->resolver->resolve(FilesDriver::class),
        };
    }
}
