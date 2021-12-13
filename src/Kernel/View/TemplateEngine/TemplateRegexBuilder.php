<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class TemplateRegexBuilder
{
    public const INCLUDE = 'include';
    public const INHERIT = 'inherit';
    public const CONTENT = 'content';
    public const BLOCK = 'block';
    public const ENDBLOCK = 'endblock';
    public const DIRECTIVES = [
        self::INCLUDE,
        self::INHERIT,
        self::CONTENT,
        self::BLOCK,
        self::ENDBLOCK,
    ];

    protected bool $parentheses = false;
    protected string $name;
    protected string $content;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function getBuilder(string $name): self
    {
        return new self($name);
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
        $definition = '/#' . preg_quote($this->name, '/');

        if ($this->parentheses) {
            $definition .= '\([\'"]';

            if (isset($this->content)) {
                $definition .= preg_quote($this->content, '/');
            } else {
                $definition .= '([a-zA-Z-_\.\/]+)';
            }

            $definition .= '[\'"]\)';
        }

        $definition .= '/';

        return $definition;
    }
}
