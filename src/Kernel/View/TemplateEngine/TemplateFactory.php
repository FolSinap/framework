<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class TemplateFactory
{
    public function create(string $path): Template
    {
        return new Template($path);
    }
}
