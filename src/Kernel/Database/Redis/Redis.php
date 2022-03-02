<?php

namespace FW\Kernel\Database\Redis;

use FW\Kernel\Exceptions\Config\ValueIsNotConfiguredException;
use FW\Kernel\Exceptions\Database\ConnectionException;
use Redis as Connection;
use RedisException;

class Redis
{
    protected Connection $connection;

    public function __construct()
    {
        $host = config('database.drivers.redis.host');
        $port = config('database.drivers.redis.port');

        if (is_null($host)) {
            throw new ValueIsNotConfiguredException('database.drivers.redis.host');
        }

        if (is_null($port)) {
            throw new ValueIsNotConfiguredException('database.drivers.redis.port');
        }

        try {
            $this->connection = new Connection();
            $this->connection->connect($host, $port);
        } catch (RedisException) {
            throw new ConnectionException(sprintf("Wasn't able to connect to Redis. Are you sure data is valid?"
                . " host - %s, port - %s", $host, $port));
        }
    }

    public function has(string $key): bool
    {
        return $this->connection->exists($key);
    }

    public function get(string $key): mixed
    {
        $value = $this->connection->get($key);

        return $value !== false ? $value : null;
    }

    public function set(string $key, mixed $value, int $timeout = null): void
    {
        if (!is_null($timeout)) {
            $this->connection->setex($key, $timeout, $value);
        } else {
            $this->connection->set($key, $value, $timeout);
        }
    }

    public function delete($key1, ...$otherKeys): void
    {
        $this->connection->del($key1, ...$otherKeys);
    }

    public function disconnect(): void
    {
        if (!$this->connection->close()) {
            throw new ConnectionException('Error closing Redis connection');
        }
    }
}
