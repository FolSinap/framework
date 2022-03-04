<?php

namespace FW\Kernel\Storage\Cache\Memcached;

use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\ICacheDriver;

class CacheItemPool implements ICacheDriver
{
    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        // TODO: Implement getItem() method.
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): iterable
    {
        // TODO: Implement getItems() method.
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        // TODO: Implement hasItem() method.
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        // TODO: Implement clear() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        // TODO: Implement deleteItem() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        // TODO: Implement deleteItems() method.
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        // TODO: Implement save() method.
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        // TODO: Implement saveDeferred() method.
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        // TODO: Implement commit() method.
    }
}
