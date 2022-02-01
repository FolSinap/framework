<?php

use Fwt\Framework\Kernel\App;

if (!function_exists('get_string_between')) {
    function get_string_between(string $string, string $start, string $end): string
    {
        $string = ' ' . $string;
        $init = strpos($string, $start);

        if ($init == 0) {
            return '';
        }

        $init += strlen($start);
        $len = strpos($string, $end, $init) - $init;

        return substr($string, $init, $len);
    }
}

if (!function_exists('env')) {
    function env(string $name, $default = null)
    {
        $var = getenv($name);

        return $var === false ? $default : $var;
    }
}

if (!function_exists('container')) {
    function container(string $name, $default = null)
    {
        return App::$app->getContainer()->get($name) ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $name, $default = null)
    {
        return App::$app->getConfig($name, $default);
    }
}

if (!function_exists('app')) {
    function app(): App
    {
        return App::$app;
    }
}
