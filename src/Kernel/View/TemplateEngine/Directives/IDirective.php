<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

interface IDirective
{
    public const DIRECTIVE_PREFIX = '#';

    public function getRegex(): string;

    public function execute(array $matches): string;

    public function getName(): string;
}
