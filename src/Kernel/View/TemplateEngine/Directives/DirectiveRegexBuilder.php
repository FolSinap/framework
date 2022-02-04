<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

use FW\Kernel\View\TemplateEngine\TemplateRegexBuilder;

class DirectiveRegexBuilder extends TemplateRegexBuilder
{
    protected string $closingTag;
    protected bool $emptyContent = false;
    protected bool $isGreedy = false;

    public function setClosingTag(string $tag): self
    {
        $this->closingTag = $tag;

        return $this;
    }

    public function letEmptyContent(bool $emptyContent = true): self
    {
        $this->emptyContent = $emptyContent;

        return $this;
    }

    public function setIsGreedy(bool $isGreedy = true): self
    {
        $this->isGreedy = $isGreedy;

        return $this;
    }

    public static function getRegexForVars(): string
    {
        return self::getBuilder()
            ->setParentheses()
            ->useQuotes(false)
            ->setBrackets('{{')
            ->useNumbers()
            ->includeForSearch('[]!&|?,.+-*/-><= ')
            ->getRegex();
    }

    public static function getRegexForVarsNoEscape(): string
    {
        return self::getBuilder()
            ->setParentheses()
            ->useQuotes(false)
            ->setBrackets('{')
            ->useNumbers()
            ->includeForSearch('[]!&|?,.+-*/-><= ')
            ->getRegex();
    }

    public function getRegex(): string
    {
        $definition = '/' . (isset($this->name) ? preg_quote($this->name, '/') : '');

        if ($this->parentheses) {
            $definition .= preg_quote($this->brackets) . ($this->useQuotes ? '[\'"]' : '');

            if (isset($this->content)) {
                $definition .= preg_quote($this->content, '/');
            } else {
                $definition .= '([a-zA-Z' . ($this->useNumbers ? '0-9' : '') . ($this->includes ?? '') . '-_\.\/]'
                    . ($this->emptyContent ? '*' : '+') . ')';
            }

            $definition .= ($this->useQuotes ? '[\'"]' : '') . preg_quote($this->closingBrackets);
        }

        if (isset($this->closingTag)) {
            $definition .= '([\S\s]*' . ($this->isGreedy ? '' : '?') . ')' . preg_quote($this->closingTag);
        }

        $definition .= '/';

        return $definition;
    }
}
