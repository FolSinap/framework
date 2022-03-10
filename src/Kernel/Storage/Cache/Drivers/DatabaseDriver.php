<?php

namespace FW\Kernel\Storage\Cache\Drivers;

use Carbon\Carbon;
use FW\Kernel\Database\ORM\ModelRepository;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use FW\Kernel\Storage\Cache\CacheItem;
use FW\Kernel\Storage\Cache\CacheModel as Cache;

class DatabaseDriver extends AbstractPool
{
    protected array $existenceMap = [];

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        if (array_key_exists($key, $this->existenceMap)) {
            $cache = $this->existenceMap[$key] ? Cache::find($key) : null;
        } else {
            $cache = Cache::find($key);
        }

        $cache = $this->deleteModelIfExpired($cache);

        if (is_null($cache)) {
            $this->existenceMap[$key] = false;

            return new CacheItem($key, null, false);
        }

        $this->existenceMap[$key] = true;

        return new CacheItem($key, unserialize($cache->payload), true);
    }

    public function getItems(array $keys = []): iterable
    {
        $models = Cache::whereIn('id', $keys)->fetch();
        $items = [];
        $forDeletion = [];

        foreach ($keys as $key) {
            $isFound = false;

            foreach ($models as $model) {
                if ($key === $model->id) {
                    if ($this->isExpired($model)) {
                        $forDeletion[] = $key;

                        break;
                    }

                    $items[$key] = new CacheItem($key, unserialize($model->payload), true);
                    $this->existenceMap[$key] = true;
                    $isFound = true;

                    break;
                }
            }

            if (!$isFound) {
                $this->existenceMap[$key] = false;
                $items[$key] = new CacheItem($key, null, false);
            }
        }

        Cache::deleteByIds(...$forDeletion);

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        $this->existenceMap[$key] = false;
        Cache::deleteByIds($key);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->existenceMap[$key] = false;
        }

        Cache::deleteByIds(...$keys);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $item->hit();

        if (array_key_exists($item->getKey(), $this->existenceMap)) {
            if ($this->existenceMap[$item->getKey()]) {
                $this->updateModel($item);
            } else {
                $this->createModel($item);
            }
        } else {
            //identity map should return old model if it has already been fetched before
            $model = Cache::find($item->getKey());

            if (is_null($model)) {
                $this->createModel($item);
            } else {
                $this->updateModel($item, $model);
            }
        }

        $this->existenceMap[$item->getKey()] = true;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $newModels = [];

        foreach ($this->deferred as $item) {
            $item->hit();

            if (array_key_exists($item->getKey(), $this->existenceMap)) {
                if ($this->existenceMap[$item->getKey()]) {
                    $this->updateModel($item);
                } else {
                    $newModels[] = $this->createDryModel($item);
                }

                $this->existenceMap[$item->getKey()] = true;

                continue;
            }

            //identity map should return old model if it has already been fetched before
            $model = Cache::find($item->getKey());

            if (is_null($model)) {
                $newModels[] = $this->createDryModel($item);
            } else {
                $this->updateModel($item, $model);
            }

            $this->existenceMap[$item->getKey()] = true;
        }

        $repository = new ModelRepository();
        $repository->insertMany($newModels);

        $this->clear();

        return true;
    }

    protected function createDryModel(CacheItemInterface $item): Cache
    {
        return Cache::createDry([
            'id' => $item->getKey(),
            'payload' => serialize($item->get()),
            'expires_at' => $item->expiration(),
        ]);
    }

    protected function createModel(CacheItemInterface $item): Cache
    {
        return Cache::create([
            'id' => $item->getKey(),
            'payload' => serialize($item->get()),
            'expires_at' => $item->expiration(),
        ]);
    }

    protected function updateModel(CacheItemInterface $item, Cache $model = null): Cache
    {
        $model = is_null($model) ? Cache::find($item->getKey()) : $model;

        $model?->update([
            'payload' => serialize($item->get()),
            'expires_at' => $item->expiration(),
        ]);

        return $model;
    }

    protected function deleteModelIfExpired(?Cache $model): ?Cache
    {
        if ($this->isExpired($model)) {
            $model->delete();

            return null;
        }

        return $model;
    }

    protected function isExpired(?Cache $model): bool
    {
        $isExpired = false;

        if (!is_null($model?->expires_at)) {
            $isExpired = $model->expires_at < Carbon::now();
        }

        return $isExpired;
    }
}
