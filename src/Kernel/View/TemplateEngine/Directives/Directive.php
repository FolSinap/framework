<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

interface Directive
{
    public const DIRECTIVE_PREFIX = '#';

    public function getRegex(): string;

    public function execute(array $matches): string;

    public function getName(): string;
}
