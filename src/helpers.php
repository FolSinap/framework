<?php

use FW\Kernel\App;

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
    function container(string $name, mixed $default = null): mixed
    {
        return App::$app->getContainer()->get($name) ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $name, bool $throw = true): mixed
    {
        return App::$app->getConfig($name, $throw);
    }
}

if (!function_exists('app')) {
    function app(): App
    {
        return App::$app;
    }
}

if (!function_exists('project_dir')) {
    function project_dir(): string
    {
        return App::$app->getProjectDir();
    }
}

if (!function_exists('array_first')) {
    function array_first(array $array): mixed
    {
        return $array[array_key_first($array)] ?? null;
    }
}

if (!function_exists('array_last')) {
    function array_last(array $array): mixed
    {
        return $array[array_key_last($array)] ?? null;
    }
}

if (!function_exists('resolve')) {
    function resolve(string $class): object
    {
        return container(\FW\Kernel\ObjectResolver::class)->resolve($class);
    }
}
