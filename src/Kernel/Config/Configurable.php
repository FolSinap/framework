<?php

namespace FW\Kernel\Config;

use FW\Kernel\Exceptions\Config\ValueIsNotConfiguredException;

trait Configurable
{
    public function get(string $key, bool $throw = true): mixed
    {
        $keys = explode('.', $key);

        $config = $this->data;

        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            } elseif ($throw) {
                throw new ValueIsNotConfiguredException(
                    ($this instanceof FileConfig ? $this->getFileName() . '.' : '')
                    . implode('.', $keys)
                );
            } else {
                return null;
            }
        }

        return $config;
    }
}
