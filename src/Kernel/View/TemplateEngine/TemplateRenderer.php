<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\AnonDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\FlashDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\AuthDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\IfDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\IncludeDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\RenderParametersDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\RenderParametersWithoutEscapeDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class TemplateRenderer
{
    public const EXECUTABLE_DIRECTIVES = [
        IncludeDirective::class,
        IfDirective::class,
        FlashDirective::class,
        AuthDirective::class,
        AnonDirective::class,
        RenderParametersDirective::class,
        RenderParametersWithoutEscapeDirective::class,
    ];

    protected ObjectResolver $resolver;

    public function __construct()
    {
        $this->resolver = new ObjectResolver();
    }

    public function render(Template $template): string
    {
        $parent = $template->getParent();

        if ($parent) {
            $parent->renderBlocks();
            $template = $parent;
        }

        $this->executeDirectives($template);

        return $template->getContent();
    }

    protected function executeDirectives(Template $template)
    {
        foreach (self::EXECUTABLE_DIRECTIVES as $class) {
            $directive = $this->resolver->resolve($class);

            $template->setContent(preg_replace_callback($directive->getRegex(),
                function ($matches) use ($directive) {
                    return $directive->execute($matches);
                }, $template->getContent()));
        }
    }
}
