<?php

namespace Fwt\Framework\Kernel\Storage;

class Cookie
{
    public function set(string $key, $value, array $options = []): void
    {
        setcookie($key, $value, $options);
    }

    public function setRaw(string $key, $value, array $options = []): void
    {
        setrawcookie($key, $value, $options);
    }

    public function get(string $key)
    {
        return $_COOKIE[$key];
    }

    public function has(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    public function unset(string $key): void
    {
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            setcookie($key, null, [
                'expires' => -1,
                'path' => '/',
            ]);
        }
    }
}
