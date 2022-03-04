<?php

namespace FW\Kernel\Storage\Cache\Redis;

use FW\Kernel\Database\Redis\Redis;
use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;

class CacheItem implements CacheItemInterface
{
    protected bool $isHit;
    protected mixed $value;
    protected int $expiresAfter;

    public function __construct(
        protected string $key,
        protected Redis $connection
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
        if (!isset($this->value)) {
            $value = $this->connection->get($this->key);

            $this->value = is_null($value) ? $value : unserialize($value);
        }

        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        if (!isset($this->isHit)) {
            $this->isHit = $this->connection->has($this->key);
        }

        return $this->isHit;
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAfter = $expiration->getTimestamp() - time();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter(DateInterval|int|null $time): static
    {
        $this->expiresAfter = $time->s;

        return $this;
    }

    public function getExpirationSeconds(): ?int
    {
        return $this->expiresAfter ?? null;
    }
}
