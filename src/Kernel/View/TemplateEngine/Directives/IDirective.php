<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

interface IDirective
{
    public const DIRECTIVE_PREFIX = '#';

    /**
     * @return string Regex that finds implementation of this directive in html file
     */
    public function getRegex(): string;

    /**
     * @param array $matches Matches that has been returned by executing regex
     * @return string Result that should go in html
     */
    public function execute(array $matches): string;

    /**
     * @return string Name of the directive
     */
    public function getName(): string;
}
