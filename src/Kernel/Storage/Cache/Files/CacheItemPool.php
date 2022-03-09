<?php

namespace FW\Kernel\Storage\Cache\Files;

use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;

class CacheItemPool extends AbstractPool
{
    protected string $dir;

    public function __construct()
    {
        $this->dir = project_dir() . '/' . config('cache.files.dir');
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
    public function commit(): bool
    {
        $success = true;

        foreach ($this->deferred as $item) {
            $success = $success && $this->save($item);
        }

        return $success;
    }
}
