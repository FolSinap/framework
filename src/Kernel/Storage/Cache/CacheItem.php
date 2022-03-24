<?php


namespace FW\Kernel\Storage\Cache;

use Carbon\Carbon;
use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;

class CacheItem implements CacheItemInterface
{
    protected ?Carbon $expiration = null;

    public function __construct(
        protected string $key,
        protected mixed $value,
        protected bool $isHit,
    ) {
    }

    public function expiration(): ?Carbon
    {
        return $this->expiration;
    }

    public function hit(): void
    {
        $this->isHit = true;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        if (!$this->isHit) {
            return null;
        }

        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiration = is_null($expiration) ? null : Carbon::createFromInterface($expiration);

        return $this;
    }

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
