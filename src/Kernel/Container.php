<?php

namespace Fwt\Framework\Kernel;

use ArrayAccess;

//todo: implement psr-11
class Container implements ArrayAccess
{
    protected array $data;
    protected static array $instances;

    protected function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function getInstance(array $data = []): self
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static($data);
        }

        return self::$instances[$class];
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function unset(string $key): void
    {
        unset($this->data[$key]);
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function toArray(): array
    {
        return $this->data;
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

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
