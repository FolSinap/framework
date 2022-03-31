<?php

namespace FW\Kernel\Config;

use ArrayAccess;

class FileConfig implements ArrayAccess
{
    use Configurable {
        get as public traitGet;
    }

    protected ?array $data;

    public function __construct(
        protected string $name
    ) {
        $this->data = null;
    }

    public static function from(string $name): static
    {
        return new static($name);
    }

    public function get(string $key, bool $throw = true): mixed
    {
        $this->load();

        return $this->traitGet($key, $throw);
    }

    public function load(): static
    {
        if (is_null($this->data)) {
            $this->data = require_once Config::getFullPathToConfig() . "/$this->name.php";
        }

        return $this;
    }

    public function getFileName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        $this->load();

        return $this->data;
    }

    public function offsetExists($offset): bool
    {
        $this->load();

        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->load();

        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->load();

        unset($this->data[$offset]);
    }
}
