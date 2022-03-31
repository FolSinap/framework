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

    public function getValue(string $key): mixed
    {
        return match ($this->connection->type($key)) {
            Connection::REDIS_LIST      => $this->lRange($key),
            Connection::REDIS_HASH      => $this->hGetAll($key),
            Connection::REDIS_SET       => $this->sMembers($key),
            Connection::REDIS_STREAM    => $this->xRead([$key]),
            Connection::REDIS_STRING    => $this->get($key),
            Connection::REDIS_ZSET      => $this->zRangeByScore($key),
            Connection::REDIS_NOT_FOUND => null,
        };
    }

    public function hGetAll(string $key): array
    {
        return $this->connection->hGetAll($key);
    }

    public function hGet(string $key, string $hashKey): ?string
    {
        $value = $this->connection->hGet($key, $hashKey);

        return $value !== false ? $value : null;
    }

    public function hExists(string $key, string $hashKey): bool
    {
        return $this->connection->hExists($key, $hashKey);
    }

    public function hSet(string $key, string $hashKey, string $value): void
    {
        $this->connection->hSet($key, $hashKey, $value);
    }

    public function lRange(string $key, int $start = 0, int $end = -1): array
    {
        return $this->connection->lRange($key, $start, $end);
    }

    public function sMembers(string $key): array
    {
        return $this->connection->sMembers($key);
    }

    public function xRead(array $streams, int|string $count = null, int|string $block = null): array
    {
        return $this->connection->xRead($streams, $count, $block);
    }

    public function zRangeByScore(string $key, int $start = null, int $end = null, array $options = []): array
    {
        return $this->connection->zRangeByScore($key, $start ?? -INF, $end ?? INF, $options);
    }

    public function zAdd(string $key, array $valueScore, array $options = []): void
    {
        $args = [];
        $args[] = $key;

        if (!empty($options)) {
            $args[] = $options;
        }

        foreach ($valueScore as $value => $score) {
            $args[] = $score;
            $args[] = $value;
        }

        $this->connection->zAdd(...$args);
    }

    public function type(string $key): int
    {
        return $this->connection->type($key);
    }

    public function delete($key1, ...$otherKeys): void
    {
        $this->connection->del($key1, ...$otherKeys);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function disconnect(): void
    {
        if (!$this->connection->close()) {
            throw new ConnectionException('Error closing Redis connection');
        }
    }
}
