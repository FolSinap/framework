<?php

namespace FW\Kernel\Storage\Handlers;

use FW\Kernel\Database\Redis;
use SessionHandlerInterface;

class RedisSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        protected Redis $connection,
        protected int $lifetime
    ) {
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        $this->connection->disconnect();

        return true;
    }

    public function destroy($id): bool
    {
        $this->connection->delete($id);

        return true;
    }

    public function gc($max_lifetime): bool
    {
        return true;
    }

    public function read($id)
    {
        return $this->connection->get($id) ?? '';
    }

    public function write($id, $data): bool
    {
        $this->connection->set($id, $data, $this->lifetime);

        return true;
    }
}