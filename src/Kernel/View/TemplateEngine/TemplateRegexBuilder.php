<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class TemplateRegexBuilder
{
    public const INCLUDE = '#include';
    public const INHERIT = '#inherit';
    public const CONTENT = '#content';
    public const BLOCK = '#block';
    public const ENDBLOCK = '#endblock';
    public const DIRECTIVES = [
        self::INCLUDE,
        self::INHERIT,
        self::CONTENT,
        self::BLOCK,
        self::ENDBLOCK,
    ];
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

    public static function getBuilder(): self
    {
        return new self();
    }

    public static function getRegexForVars(): string
    {
        return self::getBuilder()
            ->setParentheses()
            ->useQuotes(false)
            ->setBrackets('{{')
            ->getRegex();
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
            if (!in_array($bracket, array_keys(self::BRACKETS))) {
                throw new IllegalValueException($bracket, array_keys(self::BRACKETS));
            }

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
        if (!in_array($name, self::DIRECTIVES)) {
            throw new IllegalValueException($name, self::DIRECTIVES);
        }

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
                $definition .= '([a-zA-Z' . ($this->useNumbers ? '0-9' : '') . '-_\.\/]+)';
            }

            $definition .= ($this->useQuotes ? '[\'"]' : '') . preg_quote($this->closingBrackets);
        }

        $definition .= '/';

        return $definition;
    }
}
