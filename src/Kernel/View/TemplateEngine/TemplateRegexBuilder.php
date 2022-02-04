<?php

namespace FW\Kernel\View\TemplateEngine;

use FW\Kernel\Exceptions\IllegalValueException;

class TemplateRegexBuilder
{
    public const BRACKETS = [
        '(' => ')',
        '[' => ']',
        '{' => '}',
    ];

    protected bool $parentheses = false;
    protected bool $useNumbers = false;
    protected bool $useQuotes = true;
    protected string $name;
    protected string $content;
    protected string $brackets = '(';
    protected string $closingBrackets = ')';
    protected string $includes;

    public static function getBuilder(): self
    {
        return new static();
    }

    public function includeForSearch(string $includes): self
    {
        $this->includes = preg_quote($includes, '/');

        return $this;
    }

    public function setParentheses(bool $parentheses = true): self
    {
        $this->parentheses = $parentheses;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setBrackets(string $brackets): self
    {
        $arrayBrackets = str_split($brackets);
        $closingBrackets = [];

        foreach ($arrayBrackets as $bracket) {
            IllegalValueException::checkValue($bracket, array_keys(self::BRACKETS));

            $closingBrackets[] = self::BRACKETS[$bracket];
        }

        $this->brackets = $brackets;
        $this->closingBrackets = implode('', $closingBrackets);

        return $this;
    }

    public function useNumbers(bool $useNumbers = true): self
    {
        $this->useNumbers = $useNumbers;

        return $this;
    }

    public function useQuotes(bool $useQuotes = true): self
    {
        $this->useQuotes = $useQuotes;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRegex(): string
    {
        $definition = '/' . (isset($this->name) ? preg_quote($this->name, '/') : '');

        if ($this->parentheses) {
            $definition .= preg_quote($this->brackets) . ($this->useQuotes ? '[\'"]' : '');

            if (isset($this->content)) {
                $definition .= preg_quote($this->content, '/');
            } else {
                $definition .= '([a-zA-Z' . ($this->useNumbers ? '0-9' : '') . ($this->includes ?? '') . '-_\.\/]+)';
            }

            $definition .= ($this->useQuotes ? '[\'"]' : '') . preg_quote($this->closingBrackets);
        }

        $definition .= '/';

        return $definition;
    }
}
