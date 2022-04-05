<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

use FW\Kernel\View\TemplateEngine\TemplateRegexBuilder;
use FW\Kernel\View\TemplateEngine\Templates\Template;

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

        return (Template::fromName($path))->getContent();
    }

    public function getName(): string
    {
        return 'include';
    }
}
