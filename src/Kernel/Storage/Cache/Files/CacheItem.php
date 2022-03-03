<?php

namespace FW\Kernel\Storage\Cache\Files;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
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

    /**
     * @inheritDoc
     */
    public function get(): mixed
    {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        // TODO: Implement isHit() method.
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $value): static
    {
        // TODO: Implement set() method.
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        // TODO: Implement expiresAt() method.
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter(\DateInterval|int|null $time): static
    {
        // TODO: Implement expiresAfter() method.
    }
}