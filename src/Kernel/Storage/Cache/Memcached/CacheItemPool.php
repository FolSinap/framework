<?php

namespace FW\Kernel\Storage\Cache\Memcached;

use FW\Kernel\Database\Memcached;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;

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
        return new CacheItem($this->connection, $key);
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
        return $this->connection->set($item->getKey(), $item->get(), $item->getExpiresAt() ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $unlimited = [];

        foreach ($this->deferred as $item) {
            if (is_null($item->getExpiresAt())) {
                $unlimited[$item->getKey()] = $item->get();
            } else {
                $this->save($item);
            }
        }

        $this->connection->setMany($unlimited);

        return true;
    }
}
