<?php

namespace Fwt\Framework\Kernel\Config;

trait Configurable
{
    public function get(string $key)
    {
        $keys = explode('.', $key);

        $config = $this->data;

        foreach ($keys as $key) {
            $config = $config[$key];
        }

        return $config;
    }
}
