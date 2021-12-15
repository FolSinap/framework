<?php

namespace Fwt\Framework\Kernel\Console;

class Output
{
    protected const YES = ['yes', 'y'];
    protected const BLACK = "\e[30m";
    protected const RED = "\e[31m";
    protected const GREEN = "\e[32m";
    protected const YELLOW = "\e[33m";
    protected const BLUE = "\e[34m";
    protected const CLOSE_COLOR = "\033[0m";

    public function input(string $message)
    {
        $this->print($message);

        return trim(fgets(STDIN));
    }

    public function confirm(string $message): bool
    {
        $answer = $this->input($message . ' [y/n] (n): ');

        if (in_array(strtolower($answer), self::YES)) {
            return true;
        }

        return false;
    }

    public function print(string $message): void
    {
        print $message;
    }
}
