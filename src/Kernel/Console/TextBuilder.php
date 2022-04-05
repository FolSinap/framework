<?php

namespace FW\Kernel\Console;

use FW\Kernel\Exceptions\IllegalTypeException;
use ReflectionFunction;
use Traversable;

class TextBuilder
{
    protected const TAB = "\t";
    protected const NEXT_LINE = "\n";

    protected string $text = '';
    protected int $tabsCount = 0;

    public function __toString()
    {
        return $this->getText();
    }

    public static function getBuilder(): self
    {
        return new self();
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function if(bool $expression, string $text, string $else = null): self
    {
        if ($expression) {
            $this->write($text);
        } elseif (isset($else)) {
            $this->write($else);
        }

        return $this;
    }

    public function foreach($data, callable $function): self
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new IllegalTypeException($data, ['array', Traversable::class]);
        }

        foreach ($data as $key => $value) {
            $paramCount = (new ReflectionFunction($function))->getNumberOfRequiredParameters();

            if ($paramCount === 1) {
                $this->write($function($value));
            } else {
                $this->write($function($key, $value));
            }
        }

        return $this;
    }

    public function writeln(string $text): self
    {
        $this->write($text . self::NEXT_LINE);

        return $this;
    }

    public function write(string $text): self
    {
        $tabs = str_repeat(self::TAB, $this->tabsCount);

        $this->type($tabs . $text);

        return $this;
    }

    /**
     * Same as write() but all tabs are ignored
     */
    public function type(string $text): self
    {
        $this->text .= $text;

        return $this;
    }

    public function skipLines(int $number = 1): self
    {
        $lines = str_repeat(self::NEXT_LINE, $number);
        $this->text .= $lines;

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
        $this->text .= $spaces;

        return $this;
    }

    public function dropTab(int $number = 1): self
    {
        $this->tabsCount -= $number;

        return $this;
    }

    public function clearTab(): self
    {
        $this->tabsCount = 0;

        return $this;
    }
}
