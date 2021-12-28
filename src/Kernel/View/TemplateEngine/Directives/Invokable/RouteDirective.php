<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable;

use Fwt\Framework\Kernel\Routing\Router;

class RouteDirective extends AbstractInvokableDirective
{
    public function getName(): string
    {
        return 'route';
    }

    public function __invoke(...$args): string
    {
       return Router::getRouter()->resolveUrlByName(...$args);
    }
}
