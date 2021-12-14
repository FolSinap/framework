<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRegexBuilder;

class DirectiveRegexBuilder extends TemplateRegexBuilder
{
    protected string $closingTag;

    public function setClosingTag(string $tag): self
    {
        $this->closingTag = $tag;

        return $this;
    }

    public static function getRegexForVars(): string
    {
        return self::getBuilder()
            ->setParentheses()
            ->useQuotes(false)
            ->setBrackets('{{')
            ->useNumbers()
            ->includeForSearch('[]')
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
                $definition .= '([a-zA-Z' . ($this->useNumbers ? '0-9' : '') . ($this->includes ?? '') . '-_\.\/]+)';
            }

            $definition .= ($this->useQuotes ? '[\'"]' : '') . preg_quote($this->closingBrackets);
        }

        if (isset($this->closingTag)) {
            $definition .= '([\S\s]*)' . preg_quote($this->closingTag);
        }

        $definition .= '/';

        return $definition;
    }
}
