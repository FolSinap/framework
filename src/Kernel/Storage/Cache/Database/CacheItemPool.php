<?php

namespace FW\Kernel\Storage\Cache\Database;

use FW\Kernel\Database\ORM\ModelRepository;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;

class CacheItemPool extends AbstractPool
{
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
