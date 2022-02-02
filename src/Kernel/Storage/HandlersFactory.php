<?php

namespace Fwt\Framework\Kernel\Storage;

use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Storage\Handlers\FileSessionHandler;
use SessionHandlerInterface;

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
            default:
                return null;
        }
    }
}
