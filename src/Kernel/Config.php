<?php

namespace Fwt\Framework\Kernel;

class Config extends Container
{
    public const CONFIG_DIR = '/config';

    protected function __construct()
    {
        $config = $this->readConfigFiles();

        parent::__construct($config);
    }

    public function get(string $key)
    {
        $keys = explode('.', $key);

        $config = $this->data;

        foreach ($keys as $key) {
            $config = $config[$key];
        }

        return $config;
    }

    protected function readConfigFiles(): array
    {
        $dir = App::$app->getProjectDir() . self::CONFIG_DIR;
        $files = scandir($dir);
        $config = [];

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $configKey = str_replace('.php', '', $file);
            $config[$configKey] = require_once "$dir/$file";
        }

        return $config;
    }
}
