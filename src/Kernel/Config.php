<?php

namespace Fwt\Framework\Kernel;

class Config
{
    public const CONFIG_DIR = '/config';
    protected array $config;

    public function __construct(string $config)
    {
        $this->config = require_once self::CONFIG_DIR . "/$config.php";
    }
}
