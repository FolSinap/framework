<?php

namespace FW\Kernel\Database;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use FW\Kernel\Exceptions\Database\ConnectionException;
use FW\Kernel\Exceptions\RequiredArrayKeysException;
use Redis as Connection;
use RedisException;

class Redis
{
    protected Connection $connection;

    public function __construct(array $config = null)
    {
        if (!is_null($config)) {
            RequiredArrayKeysException::checkKeysExistence(['host', 'port'], $config);

            $host = $config['host'];
            $port = $config['port'];
        } else {
            $host = config('database.drivers.redis.host');
            $port = config('database.drivers.redis.port');
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

    public function getMany(array $keys): array
    {
        $values = array_map(function ($value) {
            return $value === false ? null : $value;
        }, $this->connection->mget($keys));

        return array_combine($keys, $values);
    }

    public function set(string $key, mixed $value, CarbonInterface|int $timeout = null): bool
    {
        if (!is_null($timeout)) {
            $timeout = $timeout instanceof CarbonInterface ? $timeout->diffInSeconds(Carbon::now()) : $timeout;

            return $this->connection->setex($key, $timeout, $value);
        }

        return $this->connection->set($key, $value, $timeout);
    }

    public function setMany(array $values): bool
    {
        return $this->connection->mset($values);
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
