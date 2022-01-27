<?php

namespace Fwt\Framework\Kernel\Config;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Container;
use Fwt\Framework\Kernel\FileLoader;

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

        $loader->load($dir);

        foreach ($loader->files() as $file) {
            $file = str_replace('.php', '', (basename($file)));
            $config[$file] = new FileConfig($file);
        }

        return $config;
    }

    public static function getFullPathToConfig(): string
    {
        return App::$app->getProjectDir() . self::CONFIG_DIR;
    }
}
