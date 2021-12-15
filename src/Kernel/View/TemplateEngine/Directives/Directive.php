<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

interface Directive
{
    public function getRegex(): string;

    public function execute(array $matches): string;

    public function getName(): string;
}
