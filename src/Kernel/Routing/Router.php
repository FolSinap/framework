<?php

namespace FW\Kernel\Routing;

use FW\Kernel\Exceptions\Router\UnknownRouteNameException;
use FW\Kernel\Middlewares\MiddlewareMapper;
use FW\Kernel\Pipeline;
use FW\Kernel\Response\Response;

class Router
{
    protected static self $router;
    /** @var Route[] $routes */
    protected array $routes;
    /** @var Route[] $routes */
    protected array $namedRoutes = [];

    public static function getRouter(): self
    {
        if (!isset(self::$router)) {
            self::$router = new self();
        }

        return self::$router;
    }

    public function get(string $url, $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::GET, $callback, $name);
    }

    public function post(string $url, $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::POST, $callback, $name);
    }

    public function put(string $url, $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::PUT, $callback, $name);
    }

    public function patch(string $url, $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::PATCH, $callback, $name);
    }

    public function delete(string $url, $callback, string $name = null): Route
    {
        return $this->addRoute($url, Route::DELETE, $callback, $name);
    }

    public function resolve(string $url, string $verb): Pipeline
    {
        $pipeline = new Pipeline();

        if ($route = $this->findRoute($url, $verb)) {
            $middlewares = container(MiddlewareMapper::class)
                ->mapMany(array_merge(config('app.middlewares.default', []), $route->getMiddlewares()));

            return $pipeline->through($middlewares)->addPipe($route->resolveCallback());
        } else {
            $response = Response::notFound();

            $pipeline->addPipe(function () use ($response) {
                return $response;
            });

            return $pipeline;
        }
    }

    public function resolveUrlByName(string $name, array $wildcards = []): string
    {
        if ($route = $this->findRouteByName($name)) {
            return $route->generateUrl($wildcards);
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

    public function nameRoute(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function findRoute(string $url, string $verb): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->match($url, $verb)) {
                return $route;
            }
        }

        return null;
    }

    public function findRoutesByUrl(string $url): array
    {
        $routes = [];

        foreach ($this->routes as $route) {
            if ($route->matchUrl($url)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    protected function addRoute(string $url, string $verb, $callback, string $name = null): Route
    {
        $route = new Route($url, $callback);
        $route->addVerb($verb)->name($name);

        $this->routes[] = $route;

        if ($name) {
            $this->nameRoute($name, $route);
        }

        return $route;
    }
}
