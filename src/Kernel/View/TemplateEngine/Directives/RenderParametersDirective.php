<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\View\TemplateEngine\ExpressionParser;

class RenderParametersDirective implements Directive
{
    protected ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getRegexForVars();
    }

    public function execute(array $matches): string
    {
        return htmlspecialchars($this->expressionParser->processExpression($matches[1]));
    }

    public function getName(): string
    {
        return '';
    }
}