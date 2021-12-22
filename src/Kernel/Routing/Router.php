<?php

namespace Fwt\Framework\Kernel\Routing;

use BadMethodCallException;
use Closure;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\Router\UnknownRouteNameException;
use Fwt\Framework\Kernel\Middlewares\MiddlewareMapper;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\Pipeline;
use InvalidArgumentException;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

class Router
{
    protected static self $router;
    /** @var Route[][] $routes */
    protected array $routes;
    /** @var Route[] $routes */
    protected array $namedRoutes;
    protected ObjectResolver $resolver;

    protected function __construct(ObjectResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function getRouter(ObjectResolver $resolver = null): self
    {
        if (isset(self::$router)) {
            return self::$router;
        } elseif (is_null($resolver)) {
            throw new BadMethodCallException(ObjectResolver::class . ' instance are required to create new router instance.');
        }

        self::$router = new self($resolver);

        return self::$router;
    }

    public function get(string $url, callable $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::GET, $callback, $name);
    }

    public function post(string $url, callable $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::POST, $callback, $name);
    }

    public function resolve(string $url, string $verb): Pipeline
    {
        $pipeline = new Pipeline();

        if (array_key_exists($url, $this->routes) && array_key_exists($verb, $this->routes[$url])) {
            $route = $this->routes[$url][$verb];
            $middlewares = App::$app->getContainer()->get(MiddlewareMapper::class)->mapMany($route->getMiddlewares());

            return $pipeline->through($middlewares)->addPipe($route->getCallback());
        } else {
            $response = Response::create(View::create('errors/_404.html'), 404);

            $pipeline->addPipe(function () use ($response) {
                return $response;
            });

            return $pipeline;
        }
    }

    public function resolveUrlByName(string $name): string
    {
        if ($route = $this->findRouteByName($name)) {
            return $route->getUrl();
        }

        throw new UnknownRouteNameException($name);
    }

    public function findRouteByName(string $name): ?Route
    {
        if ($this->namedRouteExists($name)) {
            return $this->namedRoutes[$name];
        }

        return null;
    }

    public function namedRouteExists(string $name): bool
    {
        return array_key_exists($name, $this->namedRoutes);
    }

    protected function addRoute(string $url, string $verb, callable $callback, string $name = null): Route
    {
        $route = new Route($url, $this->resolveCallback($callback));
        $route->addVerb($verb)->name($name);

        $this->routes[$url][$verb] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    protected function resolveCallback(callable $callback): Closure
    {
        if (is_array($callback)) {
            if (!method_exists($callback[0], $callback[1])) {
                throw new BadMethodCallException("$callback[1] doesn't exist in $callback[0]", 500);
            }

            $controller = $this->resolver->resolve($callback[0]);
            $args = $this->resolver->resolveDependencies($callback[0], $callback[1]);

            return Closure::bind(function () use ($callback, $args) {
                return $this->{$callback[1]}(...$args);
            }, $controller, $callback[0]);
        }

        return Closure::fromCallable($callback);
    }
}
