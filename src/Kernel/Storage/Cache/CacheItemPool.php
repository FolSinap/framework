<?php

namespace FW\Kernel\Storage\Cache;

use Psr\Cache\CacheItemInterface;

abstract class CacheItemPool implements ICacheDriver
{
    protected array $deferred = [];

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
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
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[] = $item;

        return true;
    }
}