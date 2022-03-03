<?php

namespace FW\Kernel\Storage\Cache\Database;

use FW\Kernel\Database\ORM\ModelRepository;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\ICacheDriver;

class CacheItemPool implements ICacheDriver
{
    protected array $deferred = [];

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
        return (new CacheItem($key))->isHit();
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
        Cache::deleteByIds($key);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        Cache::deleteByIds(...$keys);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        /** @var ?Cache $model */
        $model = $item->getCacheModel();

        if (is_null($model)) {
            return false;
        }

        $model->synchronize();

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

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $success = true;
        $newModels = [];
        $existingModels = [];

        foreach ($this->deferred as $item) {
            /** @var ?Cache $model */
            $model = $item->getCacheModel();

            if (is_null($model)) {
                $success = false;
            } else {
                if ($model->exists()) {
                    $existingModels[] = $model;
                } else {
                    $newModels[] = $model;
                }
            }
        }

        $repository = new ModelRepository();
        $repository->insertMany($newModels);

        foreach ($existingModels as $model) {
            $model->update();
        }

        return $success;
    }
}
