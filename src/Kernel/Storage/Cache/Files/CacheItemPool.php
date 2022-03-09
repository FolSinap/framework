<?php

namespace FW\Kernel\Storage\Cache\Files;

use Carbon\Carbon;
use Psr\Cache\CacheItemInterface;
use FW\Kernel\Storage\Cache\CacheItemPool as AbstractPool;
use FW\Kernel\Storage\Cache\CacheItem;

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
        $value = $this->read($key)['value'];

        if (!file_exists($this->dir . '/' . $key)) {
            return new CacheItem($key, null, false);
        }

        return new CacheItem(
            $key,
            $value,
            true
        );
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

        $item->hit();
        $content = serialize([
            'value' => $item->get(),
            'expires_at' => $item->expiration(),
        ]);

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

    protected function read(string $file): array
    {
        $file = $this->dir . '/' . $file;

        if (file_exists($file)) {
            $content = unserialize(file_get_contents($file));

            if ($this->isExpired($content['expires_at'])) {
                unlink($file);

                $content = ['value' => null, 'expires_at' => null];
            }
        } else {
            $content = ['value' => null, 'expires_at' => null];
        }

        return $content;
    }

    protected function isExpired(string $expiration): bool
    {
        if (is_null($expiration)) {
            return false;
        }

        return $expiration < Carbon::now();
    }
}
