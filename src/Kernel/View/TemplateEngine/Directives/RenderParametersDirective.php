<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

use FW\Kernel\View\TemplateEngine\ExpressionParser;

class RenderParametersDirective implements IDirective
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