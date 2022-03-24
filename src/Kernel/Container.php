<?php

namespace FW\Kernel;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;

//todo: implement psr-11
class Container implements ArrayAccess, IteratorAggregate, Countable
{
    protected static array $instances;

    protected function __construct(
        protected array $data = []
    ) {
    }

    public static function getInstance(array $data = []): static
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

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
