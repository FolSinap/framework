<?php

namespace FW\Kernel\Database\SQL;

class SqlLogger
{
    protected static bool $isOn = false;
    protected static self $instance;
    protected array $queries = [];

    private function __construct()
    {
    }

    //todo: is this class really should be a singleton?
    public static function getLogger(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function on(): void
    {
        self::$isOn = true;
    }

    public static function off(): void
    {
        self::$isOn = false;
    }

    public function log(Query $query): void
    {
        if (self::$isOn) {
            $this->queries[] = $query;
        }
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
