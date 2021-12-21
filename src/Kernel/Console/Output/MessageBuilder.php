<?php

namespace Fwt\Framework\Kernel\Console\Output;

use Traversable;

class MessageBuilder
{
    use Colorable;

    protected const TAB = "\t";
    protected const NEXT_LINE = "\n";
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

    protected string $message = '';
    protected int $tabsCount = 0;

    public function __toString()
    {
        return $this->getMessage();
    }

    public static function getBuilder(): self
    {
        return new self();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function if(bool $expression, $message): self
    {
        if ($expression) {
            $this->write($message);
        }

        return $this;
    }

    public function foreach($data, callable $function): self
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new \Exception('$data must of type array or be traversable');
        }

        foreach ($data as $key => $value) {
            $this->write($function($key, $value));
        }

        return $this;
    }

    public function writeln(string $message): self
    {
        $this->write($message . self::NEXT_LINE);

        return $this;
    }

    public function write(string $message): self
    {
        $tabs = str_repeat(self::TAB, $this->tabsCount);

        $this->type($tabs . $message);

        return $this;
    }

    public function type(string $message): self
    {
        $this->message .= $message;

        return $this;
    }

    public function skipLines(int $number = 1): self
    {
        $lines = str_repeat(self::NEXT_LINE, $number);
        $this->message .= $lines;

        return $this;
    }

    public function nextLine(): self
    {
        $this->skipLines();

        return $this;
    }

    public function tab(int $number = 1): self
    {
        $this->tabsCount += $number;

        return $this;
    }

    public function space(int $number = 1): self
    {
        $spaces = str_repeat(' ', $number);
        $this->message .= $spaces;

        return $this;
    }

    public function dropTab(int $number = 1): self
    {
        $this->tabsCount -= $number;

        return $this;
    }
}
