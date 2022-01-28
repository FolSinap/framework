<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRegexBuilder;
use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class IncludeDirective extends Directive
{
    public function getRegex(): string
    {
        return TemplateRegexBuilder::getBuilder()
            ->setParentheses()
            ->name($this->getOpeningTag())
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        $path = $matches[1];

        return (new Template($path))->getContent();
    }

    public function getName(): string
    {
        return 'include';
    }
}
