<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\View\TemplateEngine\Directives\FlashDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class TemplateRenderer
{
    public const FLASH = '#flash';
    public const EXECUTABLE_DIRECTIVES = [
        self::FLASH => FlashDirective::class,
    ];

    public function render(Template $template): string
    {
        $template->renderIncludes();
        $parent = $template->getParent();

        if ($parent) {
            $parent->renderIncludes();
            $parent->renderBlocks();
            $template = $parent;
        }

        $template->renderArgs();

        $this->executeDirectives($template);

        return $template->getContent();
    }

    protected function executeDirectives(Template $template)
    {
        foreach (self::EXECUTABLE_DIRECTIVES as $name => $class) {
            $regex = TemplateRegexBuilder::getBuilder()
                ->name($name)
                ->useNumbers()
                ->includeForSearch('?\'.')
                ->setParentheses()
                ->useQuotes(false)
                ->getRegex();

            $directive = new $class();

            $template->setContent(preg_replace_callback($regex,
                function ($matches) use ($directive) {
                    return $directive->execute($matches[1]);
                }, $template->getContent()));
        }
    }
}
