<?php

namespace FW\Kernel\View\TemplateEngine;

use FW\Kernel\View\TemplateEngine\Templates\Template;

class TemplateFactory
{
    public function create(string $path): Template
    {
        return new Template($path);
    }
}
