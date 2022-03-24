<?php

namespace FW\Kernel\Storage\Handlers;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Database;
use FW\Kernel\Database\Memcached;
use FW\Kernel\Database\Redis;
use SessionHandlerInterface;

class HandlersFactory
{
    public function __construct(
        protected FileConfig $config
    ) {
    }

    public function create(): ?SessionHandlerInterface
    {
        $driver = $this->config->get('driver');

        if (!$driver) {
            return null;
        }

        $lifetime = $this->config->get('lifetime');

        return match ($driver) {
            'files' => new FileSessionHandler(
                project_dir() . '/' . $this->config->get('files.dir'),
                $lifetime
            ),

            'redis' => new RedisSessionHandler(
                new Redis($this->config->get('redis', false)),
                $lifetime
            ),

            'memcached' => new MemcachedSessionHandler(
                new Memcached($this->config->get('memcached.servers', false)),
                $lifetime
            ),

            'database' => new DatabaseSessionHandler(
                container(Database::class),
                $lifetime,
                $this->config->get('database.table')
            ),

            'array' => new ArraySessionHandler(),

            default => null,
        };
    }
}
