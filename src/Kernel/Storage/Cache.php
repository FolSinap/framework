<?php

namespace FW\Kernel\Storage;

use FW\Kernel\Storage\Cache\DriverFactory;
use FW\Kernel\Storage\Cache\ICacheDriver;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Cache implements CacheItemPoolInterface
{
    protected ICacheDriver $driver;

    public function __construct(string $driver = null)
    {
        $factory = new DriverFactory(config('cache'));

        $this->driver = $factory->create($driver);
    }

    public static function driver(string $driver): self
    {
        return new self($driver);
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        return $this->driver->getItem($key);
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->driver->getItems($keys);
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return $this->driver->hasItem($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->driver->clear();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        return $this->driver->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        return $this->driver->deleteItems($keys);
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->driver->save($item);
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->driver->saveDeferred($item);
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        return $this->driver->commit();
    }
}
