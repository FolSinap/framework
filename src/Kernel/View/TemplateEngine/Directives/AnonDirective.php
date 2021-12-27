<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

class AnonDirective extends AuthDirective
{
    public function execute(array $matches): string
    {
        if (!$this->checkAuth($matches)) {
            return $matches[2];
        }

        return '';
    }

    public function getName(): string
    {
        return 'anon';
    }
}
