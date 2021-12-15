<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

class RenderParametersWithoutEscapeDirective extends RenderParametersDirective
{
    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getRegexForVarsNoEscape();
    }

    public function execute(array $matches): string
    {
        return $this->expressionParser->processExpression($matches[1]);
    }
}
