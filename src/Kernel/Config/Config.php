<?php

namespace Fwt\Framework\Kernel\Config;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Container;

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
        $files = scandir($dir);
        $config = [];

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $file = str_replace('.php', '', $file);
            $config[$file] = new FileConfig($file);
        }

        return $config;
    }

    public static function getFullPathToConfig(): string
    {
        return App::$app->getProjectDir() . self::CONFIG_DIR;
    }
}
