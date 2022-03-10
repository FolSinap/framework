<?php

namespace FW\Kernel\Storage\Handlers;

use SessionHandlerInterface;

class ArraySessionHandler implements SessionHandlerInterface
{
    protected static array $storage = [];

    public function close(): bool
    {
        return true;
    }

    public function destroy($id): bool
    {
        unset(self::$storage[$id]);

        return true;
    }

    public function gc($max_lifetime): bool
    {
        return true;
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function read($id): string
    {
        return self::$storage[$id] ?? '';
    }

    public function write($id, $data): bool
    {
        self::$storage[$id] = $data;

        return true;
    }
}
