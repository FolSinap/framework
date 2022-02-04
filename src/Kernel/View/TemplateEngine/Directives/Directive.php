<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

abstract class Directive implements IDirective
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
