<?php

namespace Fwt\Framework\Kernel\Storage;

use Fwt\Framework\Kernel\Storage\Handlers\HandlersFactory;

class Session
{
    protected static self $instance;

    protected function __construct()
    {
        $factory = new HandlersFactory(config('session'));

        if (!is_null($handler = $factory->create())) {
            session_set_save_handler($handler);
        }

        if (!session_start()) {
            throw new \RuntimeException('Failed to start the session.');
        }
    }

    public static function start(): self
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$key];
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function clean(): void
    {
        session_unset();
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
