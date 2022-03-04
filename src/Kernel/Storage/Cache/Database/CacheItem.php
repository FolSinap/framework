<?php

namespace FW\Kernel\Storage\Cache\Database;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;

class CacheItem implements CacheItemInterface
{
    protected ?Cache $model;
    protected ?CarbonInterface $expiresAt = null;

    public function __construct(
        protected string $key
    ) {
    }

    public function getCacheModel(): ?Cache
    {
        return $this->model;
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

        $this->deleteModelIfExpired();

        $value = $this->model?->payload;

        return is_null($value) ? $value : unserialize($value);
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        if (!isset($this->model)) {
            $this->get();
        }

        $this->deleteModelIfExpired();

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
            $this->model = Cache::createDry([
                'id' => $this->key,
                'payload' => serialize($value),
                'expires_at' => $this->expiresAt,
            ]);
        } else {
            $this->model->payload = serialize($value);
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

        $expiration = is_null($expiration) ? null : Carbon::createFromInterface($expiration);

        if (is_null($this->model)) {
            $this->model = Cache::createDry([
                'id' => $this->key,
                'expires_at' => $expiration,
            ]);
        } else {
            $this->model->expires_at = $expiration;
        }

        $this->expiresAt = $expiration;

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

    protected function deleteModelIfExpired(): void
    {
        if ($this->isExpired()) {
            $this->model->delete();
            $this->model = null;
        }
    }

    protected function isExpired(): bool
    {
        $isExpired = false;

        if (!is_null($this->model?->expires_at)) {
            $isExpired = $this->model->expires_at < Carbon::now();
        }

        return $isExpired;
    }
}
