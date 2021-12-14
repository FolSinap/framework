<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;
use Fwt\Framework\Kernel\View\VariableContainer;

class TemplateFactory
{
    public function create(string $path, VariableContainer $container): Template
    {
        return new Template($path, $container);
    }
}
