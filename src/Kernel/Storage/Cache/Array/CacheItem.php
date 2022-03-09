<?php

namespace FW\Kernel\Storage\Cache\Array;

use DateTimeInterface;
use DateInterval;
use FW\Kernel\Storage\Cache\CacheItem as AbstractCacheItem;

class CacheItem extends AbstractCacheItem
{
    protected static array $storage = [];
    protected mixed $value;

    public static function save(self $item): void
    {
        self::$storage[$item->getKey()] = $item->value;
    }

    public static function delete(string $key): void
    {
        unset(self::$storage[$key]);
    }

    public function get(): mixed
    {
        return self::$storage[$this->key] ?? null;
    }

    public function isHit(): bool
    {
        return array_key_exists($this->key, self::$storage);
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        return $this;
    }
}
