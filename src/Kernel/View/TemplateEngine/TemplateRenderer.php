<?php

namespace FW\Kernel\View\TemplateEngine;

use FW\Kernel\Exceptions\InterfaceNotFoundException;
use FW\Kernel\ObjectResolver;
use FW\Kernel\View\TemplateEngine\Directives\AnonDirective;
use FW\Kernel\View\TemplateEngine\Directives\AuthDirective;
use FW\Kernel\View\TemplateEngine\Directives\IDirective;
use FW\Kernel\View\TemplateEngine\Directives\ForeachDirective;
use FW\Kernel\View\TemplateEngine\Directives\Invokable\CsrfDirective;
use FW\Kernel\View\TemplateEngine\Directives\Invokable\FlashDirective;
use FW\Kernel\View\TemplateEngine\Directives\Invokable\MethodDirective;
use FW\Kernel\View\TemplateEngine\Directives\Invokable\RouteDirective;
use FW\Kernel\View\TemplateEngine\Directives\IfDirective;
use FW\Kernel\View\TemplateEngine\Directives\IncludeDirective;
use FW\Kernel\View\TemplateEngine\Directives\RenderParametersDirective;
use FW\Kernel\View\TemplateEngine\Directives\RenderParametersWithoutEscapeDirective;
use FW\Kernel\View\TemplateEngine\Templates\Template;

class TemplateRenderer
{
    public const EXECUTABLE_DIRECTIVES = [
        IncludeDirective::class,
        CsrfDirective::class,
        FlashDirective::class,
        AuthDirective::class,
        AnonDirective::class,
        ForeachDirective::class,
        IfDirective::class,
        RouteDirective::class,
        MethodDirective::class,
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

        $template->setContent($this->executeDirectives($template->getContent()));

        return $template->getContent();
    }

    public function executeDirectives(string $content): string
    {
        foreach (self::EXECUTABLE_DIRECTIVES as $class) {
            $directive = $this->resolver->resolve($class);

            if (!$directive instanceof IDirective) {
                throw new InterfaceNotFoundException(get_class($directive), IDirective::class);
            }

            $content = preg_replace_callback($directive->getRegex(),
                function ($matches) use ($directive) {
                    return $directive->execute($matches);
                }, $content);
        }

        return $content;
    }
}
