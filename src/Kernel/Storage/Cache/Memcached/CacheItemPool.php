<?php

namespace FW\Kernel\Storage\Cache\Memcached;

use FW\Kernel\Database\Memcached;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use FW\Kernel\Storage\Cache\CacheItem;

class CacheItemPool extends AbstractPool
{
    public function __construct(
        protected Memcached $connection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        return new CacheItem(
            $key,
            $this->connection->get($key),
            $this->connection->has($key)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        return $this->connection->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $this->connection->deleteMany(...$keys);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $item->hit();

        return $this->connection->set($item->getKey(), $item->get(), $item->expiration() ?? 0);
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
                $unlimited[$item->getKey()] = $item->get();
            } else {
                $success = $success && $this->save($item);
            }
        }

        return $success && $this->connection->setMany($unlimited);
    }
}
