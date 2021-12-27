<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

abstract class AbstractDirective implements Directive
{
    public function getOpeningTag(): string
    {
        return self::DIRECTIVE_PREFIX . $this->getName();
    }

    public function getClosingTag(): string
    {
        return self::DIRECTIVE_PREFIX . 'end' . $this->getName();
    }
}
