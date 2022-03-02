<?php

namespace FW\Kernel\Storage\Cache\Redis;

use FW\Kernel\Database\Redis\Redis;
use FW\Kernel\Storage\Cache\ICacheDriver;
use Psr\Cache\CacheItemInterface;

class CacheItemPool implements ICacheDriver
{
    protected array $deferred = [];

    public function __construct(
        protected Redis $connection
    ) {
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
    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return (new CacheItem($key, $this->connection))->isHit();
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->deferred = [];

        return true;
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
        $this->connection->set($item->getKey(), $item->get(), $item->getExpirationSeconds());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

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