<?php

namespace FW\Kernel\Storage\Cache\Files;

use Carbon\Carbon;
use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;

class CacheItem implements CacheItemInterface
{
    protected string $file;
    protected array $content;

    public function __construct(
        protected string $key
    ) {
        $this->file = project_dir() . '/' . config('cache.files.dir') . '/' . $this->key;
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
        $this->deleteIfExpired();

        return $this->content['value'];
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        $isHit = file_exists($this->file) && !$this->isExpired();
        $this->deleteIfExpired();

        return $isHit;
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $value): static
    {
        $this->initContent();
        $this->content['value'] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->initContent();
        $this->content['expires_at'] = is_null($expiration) ? null : Carbon::createFromInterface($expiration);

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

    public function getContent(): array
    {
        $this->initContent();

        return $this->content;
    }

    protected function deleteIfExpired(): void
    {
        if ($this->isExpired()) {
            unlink($this->file);

            $this->content = ['value' => null, 'expires_at' => null];
        }
    }

    protected function isExpired(): bool
    {
        $this->initContent();
        $expiresAt = $this->content['expires_at'];

        if (is_null($expiresAt)) {
            return false;
        }

        return $expiresAt < Carbon::now();
    }

    protected function initContent(): void
    {
        if (isset($this->content)) {
            return;
        }

        if (file_exists($this->file)) {
            $this->content = unserialize(file_get_contents($this->file));
        } else {
            $this->content = ['value' => null, 'expires_at' => null];
        }
    }
}
