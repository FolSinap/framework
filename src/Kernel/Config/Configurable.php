<?php

namespace FW\Kernel\Config;

trait Configurable
{
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);

        $config = $this->data;

        foreach ($keys as $key) {
            $config = $config[$key] ?? $default;
        }

        return $config;
    }
}
