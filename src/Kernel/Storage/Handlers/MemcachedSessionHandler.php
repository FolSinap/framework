<?php

namespace FW\Kernel\Storage\Handlers;

use Carbon\Carbon;
use FW\Kernel\Database\Memcached;
use SessionHandlerInterface;

class MemcachedSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        protected Memcached $connection,
        protected int $lifetime,
    ) {
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy($id): bool
    {
        return $this->connection->delete($id);
    }

    public function gc($max_lifetime): bool
    {
        return true;
    }

    public function read($id): string
    {
        return $this->connection->get($id) ?? '';
    }

    public function write($id, $data): bool
    {
        return $this->connection->set($id, $data, Carbon::now()->addSeconds($this->lifetime));
    }
}
