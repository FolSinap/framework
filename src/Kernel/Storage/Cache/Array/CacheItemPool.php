<?php

namespace FW\Kernel\Storage\Cache\Array;

use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use FW\Kernel\Storage\Cache\CacheItem;
use Psr\Cache\CacheItemInterface;

class CacheItemPool extends AbstractPool
{
    protected static array $storage = [];

    public function getItem(string $key): CacheItemInterface
    {
        if (array_key_exists($key, self::$storage)) {
            return new CacheItem($key, self::$storage[$key], true);
        }

        return new CacheItem($key, null, false);
    }

    public function deleteItem(string $key): bool
    {
        unset(self::$storage[$key]);

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
        self::$storage[$item->getKey()] = $item->get();

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        $this->clear();

        return true;
    }
}
