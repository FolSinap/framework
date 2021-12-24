<?php

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
