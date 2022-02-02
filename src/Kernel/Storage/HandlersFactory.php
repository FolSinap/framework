<?php

namespace Fwt\Framework\Kernel\Storage;

use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Storage\Handlers\DatabaseSessionHandler;
use Fwt\Framework\Kernel\Storage\Handlers\FileSessionHandler;
use Fwt\Framework\Kernel\Storage\Handlers\RedisSessionHandler;
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

    protected FileConfig $config;

    public function __construct(FileConfig $config)
    {
        $this->config = $config;
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
