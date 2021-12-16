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
    protected const WHITE = "\e[37m";

    protected const ON_BLACK = "\e[40m";
    protected const ON_RED = "\e[41m";
    protected const ON_GREEN = "\e[42m";
    protected const ON_YELLOW = "\e[43m";
    protected const ON_BLUE = "\e[44m";
    protected const ON_WHITE = "\e[47m";
    protected const CLOSE_COLOR = "\033[0m";
    protected const NEXT_LINE = "\n";

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

    public function success(string $message): void
    {
        $this->print(self::BLACK . self::ON_GREEN . $message . self::CLOSE_COLOR . self::NEXT_LINE);
    }

    public function info(string $message): void
    {
        $this->print(self::BLUE . $message . self::CLOSE_COLOR . self::NEXT_LINE);
    }

    public function error(string $message): void
    {
        $this->print(self::WHITE . self::ON_RED . $message . self::CLOSE_COLOR . self::NEXT_LINE);
    }

    public function warning(string $message): void
    {
        $this->print(self::BLACK . self::ON_YELLOW . $message . self::CLOSE_COLOR . self::NEXT_LINE);
    }

    public function print(string $message): void
    {
        print $message;
    }
}
