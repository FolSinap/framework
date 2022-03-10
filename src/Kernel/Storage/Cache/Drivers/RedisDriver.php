<?php

namespace FW\Kernel\Storage\Cache\Drivers;

use FW\Kernel\Database\Redis;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use FW\Kernel\Storage\Cache\CacheItem;

class RedisDriver extends AbstractPool
{
    protected array $deferred = [];

    public function __construct(
        protected Redis $connection,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        $value = $this->connection->get($key);

        return new CacheItem(
            $key,
            is_null($value) ? null : unserialize($value),
            $this->connection->has($key)
        );
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
        $item->hit();

        return $this->connection->set($item->getKey(), serialize($item->get()), $item->expiration());
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $unlimited = [];
        $success = true;

        foreach ($this->deferred as $item) {
            $item->hit();

            if (is_null($item->expiration())) {
                $unlimited[$item->getKey()] = serialize($item->get());
            } else {
                $success = $success && $this->save($item);
            }
        }

        $this->clear();

        return $success && $this->connection->setMany($unlimited);
    }

    public function getItems(array $keys = []): iterable
    {
        $values = $this->connection->getMany($keys);
        $items = [];

        foreach ($values as $key => $value) {
            $items[$key] = new CacheItem($key, $value, !is_null($value));
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return $this->connection->has($key);
    }
}
