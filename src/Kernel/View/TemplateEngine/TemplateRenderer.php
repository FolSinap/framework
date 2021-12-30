<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\Exceptions\InterfaceNotFoundException;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\AnonDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\AuthDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\Directive;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\ForeachDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable\FlashDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable\MethodDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable\RouteDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\IfDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\IncludeDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\RenderParametersDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\RenderParametersWithoutEscapeDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class TemplateRenderer
{
    public const EXECUTABLE_DIRECTIVES = [
        IncludeDirective::class,
        FlashDirective::class,
        AuthDirective::class,
        AnonDirective::class,
        ForeachDirective::class,
        MethodDirective::class,
        RenderParametersDirective::class,
        RenderParametersWithoutEscapeDirective::class,
        //todo: should this directive be at the end? (conflicts with ForeachDirective::class)
        RouteDirective::class,
        IfDirective::class,
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

            if (!$directive instanceof Directive) {
                throw new InterfaceNotFoundException(get_class($directive), Directive::class);
            }

            $template->setContent(preg_replace_callback($directive->getRegex(),
                function ($matches) use ($directive) {
                    return $directive->execute($matches);
                }, $template->getContent()));
        }
    }
}
