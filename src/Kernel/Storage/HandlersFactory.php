<?php

namespace Fwt\Framework\Kernel\Storage;

use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Storage\Handlers\FileSessionHandler;
use Fwt\Framework\Kernel\Storage\Handlers\RedisSessionHandler;
use SessionHandlerInterface;

class HandlersFactory
{
    protected const FILES = 'files';
    protected const REDIS = 'redis';
    protected const DRIVERS = [
        self::FILES => FileSessionHandler::class,
        self::REDIS => RedisSessionHandler::class,
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
        $path = project_dir() . '/' . $this->config->get('filepath', 'storage/session');
        $lifetime = $this->config->get('lifetime');

        switch ($driver) {
            case self::FILES:
                return new FileSessionHandler($path, $lifetime);
            case self::REDIS:
                return new RedisSessionHandler($lifetime);
            default:
                return null;
        }
    }
}
