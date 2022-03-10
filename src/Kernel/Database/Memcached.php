<?php

namespace FW\Kernel\Database;

use Carbon\Carbon;
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

        if (!$this->connection->addServers($servers ?? config('database.drivers.memcached.servers'))) {
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

    public function keys(): array
    {
        $keys = $this->connection->getAllKeys();

        if ($keys === false) {
            throw new ConnectionException('Couldn\'t fetch keys from memcached.');
        }

        return $keys;
    }

    public function set(string $key, mixed $value, CarbonInterface|int $expiration = 0): bool
    {
        if ($expiration instanceof CarbonInterface) {
            $diff = Carbon::now()->diffInSeconds($expiration);

            if ($diff > 60 * 60 * 24 * 30) {
                $expiration = $expiration->getTimestamp();
            } else {
                $expiration = $diff;
            }
        }

        return $this->connection->set($key, $value, $expiration);
    }

    public function setMany(array $values): bool
    {
        return $this->connection->setMulti($values);
    }

    public function has(string $key): bool
    {
        $this->connection->get($key);

        return $this->connection->getResultCode() !== Connection::RES_NOTFOUND;
    }

    public function get(string $key): mixed
    {
        $value = $this->connection->get($key);

        return $this->connection->getResultCode() === Connection::RES_NOTFOUND ? null : $value;
    }

    public function delete(string $key): bool
    {
        return $this->connection->delete($key);
    }

    public function deleteMany(string ...$keys): void
    {
        $this->connection->deleteMulti($keys);
    }
}
