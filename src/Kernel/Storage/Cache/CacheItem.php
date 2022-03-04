<?php


namespace FW\Kernel\Storage\Cache;

use Psr\Cache\CacheItemInterface;

abstract class CacheItem implements CacheItemInterface
{
    public function __construct(
        protected string $key
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return $this->key;
    }
}