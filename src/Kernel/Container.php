<?php

namespace Fwt\Framework\Kernel;

use ArrayAccess;

class Container implements ArrayAccess
{
    protected array $data;
    protected static self $container;

    protected function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function getInstance(array $data = []): self
    {
        if (isset(self::$container)) {
            return self::$container;
        }

        self::$container = new self($data);

        return self::$container;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
