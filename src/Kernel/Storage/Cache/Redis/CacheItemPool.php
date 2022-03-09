<?php

namespace FW\Kernel\Storage\Cache\Redis;

use FW\Kernel\Database\Redis;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;

class CacheItemPool extends AbstractPool
{
    protected Redis $connection;

    public function __construct()
    {
        $this->connection = new Redis(config('cache.redis'));
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        return new CacheItem($key, $this->connection);
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        $this->connection->delete($key);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $this->connection->delete(...$keys);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $this->connection->set($item->getKey(), serialize($item->get()), $item->getExpirationSeconds());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        return true;
    }
}