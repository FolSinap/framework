<?php

namespace FW\Kernel\Storage\Handlers;

use FW\Kernel\Database\Redis;
use SessionHandlerInterface;

class RedisSessionHandler implements SessionHandlerInterface
{
    protected Redis $redis;
    protected int $lifetime;

    public function __construct(int $lifetime = null)
    {
        $this->lifetime = $lifetime ?? 15 * 60;
    }

    public function open($path, $name): bool
    {
        $this->redis = new Redis();

        return true;
    }

    public function close(): bool
    {
        $this->redis->disconnect();

        return true;
    }

    public function destroy($id): bool
    {
        $this->redis->delete($id);

        return true;
    }

    public function gc($max_lifetime): bool
    {
        return true;
    }

    public function read($id)
    {
        return $this->redis->get($id) ?? '';
    }

    public function write($id, $data): bool
    {
        $this->redis->set($id, $data, $this->lifetime);

        return true;
    }
}