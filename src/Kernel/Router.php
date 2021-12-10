<?php

namespace Fwt\Framework\Kernel;

use BadMethodCallException;
use Closure;
use Fwt\Framework\Kernel\Exceptions\Router\UnknownRouteNameException;
use InvalidArgumentException;
use Fwt\Framework\Kernel\View\View;
use Fwt\Framework\Kernel\Response\Response;

class Router
{
    public const GET = 'GET';
    public const POST = 'POST';

    protected static self $router;
    protected array $routes;
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

    public function get(string $url, $data, string $name = null): void
    {
        $this->routes[$url][self::GET] = $data;

        if ($name) {
            $this->namedRoutes[$name] = $url;
        }
    }

    public function post(string $url, $data, string $name = null): void
    {
        $this->routes[$url][self::POST] = $data;

        if ($name) {
            $this->namedRoutes[$name] = $url;
        }
    }

    public function resolve(string $path, string $method): Closure
    {
        $routes = $this->routes;

        if (array_key_exists($path, $routes) && array_key_exists($method, $routes[$path])) {
            return $this->resolveCallback($routes[$path][$method]);
        } else {
            $response = Response::create(View::create('errors/_404.html'), 404);

            return function () use ($response) {
                return $response;
            };
        }
    }

    public function resolveUrlByName(string $name): string
    {
        $routes = $this->namedRoutes;

        if (array_key_exists($name, $routes)) {
            return $routes[$name];
        }

        throw new UnknownRouteNameException($name);
    }

    public function namedRouteExists(string $name): bool
    {
        return array_key_exists($name, $this->namedRoutes);
    }

    protected function resolveCallback($callback): Closure
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

        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Invalid router function');
        }

        return Closure::fromCallable($callback);
    }
}
