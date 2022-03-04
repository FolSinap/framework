<?php

namespace FW\Kernel\Database;

use Carbon\CarbonInterface;
use FW\Kernel\Exceptions\Database\ConnectionException;
use Memcached as Connection;

class Memcached
{
    protected Connection $connection;

    public function __construct(array $servers = null)
    {
        $this->connection = new Connection();
        $this->connection->resetServerList();

        if (is_null($servers)) {
            $servers = config('cache.memcached.servers');
        }

        if (!$this->connection->addServers($servers)) {
            throw new ConnectionException('Error connecting to Memcached, check hosts and ports data.');
        }
    }

    public static function connect(string $host, int $port, int $weight = null): self
    {
        $servers = [
            [
                'host' => $host,
                'port' => $port,
                'weight' => $weight ?? 100,
            ]
        ];

        return new self($servers);
    }

    public function set(string $key, mixed $value, CarbonInterface|int $expiration = 0): bool
    {
        $expiration = $expiration instanceof CarbonInterface ? $expiration->getTimestamp() : $expiration;

        return $this->connection->set($key, $value, $expiration);
    }

    public function get(string $key): mixed
    {
        $value = $this->connection->get($key);

        return $value === false ? null : $value;
    }

    public function delete(string $key): bool
    {
        return $this->connection->delete($key);
    }
}
