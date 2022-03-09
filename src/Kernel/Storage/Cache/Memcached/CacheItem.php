<?php

namespace FW\Kernel\Storage\Cache\Memcached;

use Carbon\Carbon;
use FW\Kernel\Database\Memcached;
use DateTimeInterface;
use DateInterval;
use FW\Kernel\Storage\Cache\CacheItem as AbstractCacheItem;

class CacheItem extends AbstractCacheItem
{
    protected mixed $value;
    protected ?Carbon $expiresAt = null;

    public function __construct(
        protected Memcached $connection,
        string $key
    ) {
        parent::__construct($key);
    }

    public function getExpiresAt(): ?Carbon
    {
        return $this->expiresAt;
    }

    /**
     * @inheritDoc
     */
    public function get(): mixed
    {
        if (!isset($this->value)) {
            $this->value = $this->connection->get($this->key);
        }

        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        return $this->connection->has($this->key);
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
        $this->expiresAt = is_null($expiration) ? null : Carbon::createFromInterface($expiration);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter(DateInterval|int|null $time): static
    {
        $expiration = match (true) {
            $time instanceof DateInterval => Carbon::now()->add($time),
            is_int($time) => Carbon::now()->addSeconds($time),
            default => null,
        };

        return $this->expiresAt($expiration);
    }
}
