<?php

namespace FW\Kernel\Config;

use FW\Kernel\Container;
use FW\Kernel\FileLoader;

class Config extends Container
{
    use Configurable;

    public const CONFIG_DIR = '/config';

    protected function __construct()
    {
        $config = $this->readConfigFiles();

        parent::__construct($config);
    }

    protected function readConfigFiles(): array
    {
        $dir = self::getFullPathToConfig();
        $loader = new FileLoader();
        $config = [];

        $loader->allowedExtensions(['.php'])->ignoreHidden()->load($dir);

        foreach ($loader->baseNames() as $file) {
            $file = str_replace('.php', '', $file);
            $config[$file] = new FileConfig($file);
        }

        return $config;
    }

    public static function getFullPathToConfig(): string
    {
        return project_dir() . self::CONFIG_DIR;
    }
}
