<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\MessageBuilder;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Routing\Route;
use Fwt\Framework\Kernel\Routing\Router;

class RouterCommand extends Command
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getName(): string
    {
        return 'router';
    }

    public function getDescription(): string
    {
        return 'Debug router.';
    }

    public function getOptionalParameters(): array
    {
        return [
            'path' => 'Path for matching',
            'method' => 'HTTP verb',
        ];
    }

    public function execute(Input $input, Output $output): void
    {
        $path = $this->getParameters($input)['path'];
        $method = $this->getParameters($input)['method'];

        $routes = $path ? $this->matchRoute($path, $method) : $this->getRoutes();

        $this->renderRoutes($output, $routes);
    }

    /**
     * @param array<Route> $routes
     */
    protected function renderRoutes(Output $output, array $routes): void
    {
        if (empty($routes)) {
            $output->warning('No routes found');

            return;
        }

        $percentage = [20, 10, 10, 15, 35];

        $output->print(
            MessageBuilder::getBuilder()
                ->drawLine()
                ->middle('Routes')
                ->drawLine()
                ->separate($percentage, ['Url', 'Name', 'Verbs', 'Middleware', 'Callback'])
                ->nextLine()
                ->drawLine('#')
                ->skipLines()
                ->foreach($routes, function (Route $route) use ($percentage) {
                    $callback = $route->getCallback();
                    if (is_array($callback)) {
                        $callback = "$callback[0]::$callback[1]";
                    } elseif (is_string($callback)) {
                        $callback = "$callback()";
                    } else {
                        $callback = 'User defined callback';
                    }

                    $content = [
                        $route->getUrl(),
                        $route->getName() ?? 'NOT DEFINED',
                        implode(', ', $route->getVerbs()),
                        implode(', ', $route->getMiddlewares()),
                        $callback,
                    ];

                    return MessageBuilder::getBuilder()
                        ->separate($percentage, $content)
                        ->nextLine()
                        ->drawLine();
                })
        );
    }

    /**
     * @return array<Route>
     */
    protected function getRoutes(): array
    {
        return $this->router->routes();
    }

    /**
     * @return array<Route>
     */
    protected function matchRoute(string $path, string $method = null): array
    {
        if ($method) {
            $route = $this->router->findRoute($path, $method);

            return is_null($route) ? [] : [$route];
        }

        return $this->router->findRoutesByUrl($path);
    }
}
