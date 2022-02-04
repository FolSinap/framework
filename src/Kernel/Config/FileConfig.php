<?php

namespace FW\Kernel\Config;

use ArrayAccess;

class FileConfig implements ArrayAccess
{
    use Configurable;

    protected array $data;

    public function __construct(string $name)
    {
        $this->data = require Config::getFullPathToConfig() . "/$name.php";
    }

    public static function from(string $name): self
    {
        return new static($name);
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
}
