<?php

namespace FW\Kernel\Storage\Cache\Files;

use FW\Kernel\Storage\Cache\ICacheDriver;
use Psr\Cache\CacheItemInterface;

class CacheItemPool implements ICacheDriver
{
    protected array $deferred = [];
    protected string $dir;

    public function __construct()
    {
        $this->dir = project_dir() . '/' . config('cache.dir');
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        return new CacheItem($key);
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
    public function deleteItem(string $key): bool
    {
        $file = "$this->dir/$key";

        if (!file_exists($file)) {
            return false;
        }

        return unlink($file);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            $success = $success && $this->deleteItem($key);
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }

        $content = serialize($item->getContent());

        return file_put_contents($this->dir . '/' . $item->getKey(), $content) !== false;
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
        $success = true;

        foreach ($this->deferred as $item) {
            $success = $success && $this->save($item);
        }

        return $success;
    }
}
