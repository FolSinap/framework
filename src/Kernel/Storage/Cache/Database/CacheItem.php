<?php

namespace FW\Kernel\Storage\Cache\Database;

use Carbon\Carbon;
use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;

class CacheItem implements CacheItemInterface
{
    protected ?Cache $model;

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
        if (!isset($this->model)) {
            $this->model = Cache::find($this->key);
        }

        return $this->model?->payload;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        if (!isset($this->model)) {
            $this->get();
        }

        return !is_null($this->model);
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $value): static
    {
        if (!isset($this->model)) {
            $this->get();
        }

        if (is_null($this->model)) {
            $this->model = Cache::createDry(['id' => $this->key, 'payload' => $value]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        if (!isset($this->model)) {
            $this->get();
        }

        if (is_null($this->model)) {
            $this->model = Cache::createDry([
                'id' => $this->key,
                'expires_at' => is_null($expiration) ? null : Carbon::createFromInterface($expiration),
            ]);
        }

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