<?php

namespace FW\Kernel\View\TemplateEngine\Directives\Invokable;

use FW\Kernel\Routing\Router;

class RouteDirective extends InvokableDirective
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
