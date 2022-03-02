<?php

namespace FW\Kernel\Storage\Handlers;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Database;
use FW\Kernel\Exceptions\IllegalValueException;
use SessionHandlerInterface;

class HandlersFactory
{
    protected const FILES = 'files';
    protected const REDIS = 'redis';
    protected const DATABASE = 'database';
    protected const DRIVERS = [
        self::FILES => FileSessionHandler::class,
        self::REDIS => RedisSessionHandler::class,
        self::DATABASE => DatabaseSessionHandler::class,
    ];

    public function __construct(
        protected FileConfig $config
    ) {
    }

    public function create(): ?SessionHandlerInterface
    {
        $driver = $this->config->get('driver');

        if (!$driver) {
            return null;
        }

        IllegalValueException::checkValue($driver, array_keys(self::DRIVERS));
        $lifetime = $this->config->get('lifetime');

        switch ($driver) {
            case self::FILES:
                $path = project_dir() . '/' . $this->config->get('filepath', 'storage/session');

                return new FileSessionHandler($path, $lifetime);
            case self::REDIS:
                return new RedisSessionHandler($lifetime);
            case self::DATABASE:
                $table = $this->config->get('table');

                return new DatabaseSessionHandler(container(Database::class), $lifetime, $table);
            default:
                return null;
        }
    }
}
