<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

class TemplateFactory
{
    public function create(string $path): Template
    {
        return new Template($path);
    }
}
