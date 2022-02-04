<?php

namespace FW\Kernel\Exceptions\View;

use LogicException;

class TemplateNotFoundException extends LogicException
{
    public function __construct(string $template)
    {
        parent::__construct("$template is not found");
    }
}