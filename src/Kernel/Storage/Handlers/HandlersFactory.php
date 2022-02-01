<?php

namespace Fwt\Framework\Kernel\Storage\Handlers;

use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use SessionHandler;

class HandlersFactory
{
    protected const FILES = 'files';
    protected const DRIVERS = [
        self::FILES => FileSessionHandler::class,
    ];

    protected FileConfig $config;

    public function __construct(FileConfig $config)
    {
        $this->config = $config;
    }

    public function create(): ?SessionHandler
    {
        $driver = $this->config->get('driver');

        if (!$driver) {
            return null;
        }

        IllegalValueException::checkValue($driver, array_keys(self::DRIVERS));
        $path = $this->config->get('filepath', 'storage/session');

        switch ($driver) {
            case self::FILES:
                return new FileSessionHandler($path);
            default:
                return null;
        }
    }
}
