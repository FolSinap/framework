<?php

namespace FW\Kernel\Storage\Cache\Array;

use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use Psr\Cache\CacheItemInterface;

class CacheItemPool extends AbstractPool
{
    public function getItem(string $key): CacheItemInterface
    {
        return new CacheItem($key);
    }

    public function deleteItem(string $key): bool
    {
        CacheItem::delete($key);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        CacheItem::save($item);

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        return true;
    }
}
