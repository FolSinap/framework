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
        //todo: fix "?? 'null'" part
        return htmlspecialchars($this->expressionParser->processExpression($matches[1]) ?? 'null');
    }

    public function getName(): string
    {
        return '';
    }
}