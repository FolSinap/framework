<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\InterfaceNotFoundException;
use Fwt\Framework\Kernel\Exceptions\Middleware\MiddlewareNotFoundException;
use Fwt\Framework\Kernel\FileLoader;
use Fwt\Framework\Kernel\ObjectResolver;

class MiddlewareMapper
{
    protected ObjectResolver $resolver;
    protected array $middlewares;
    protected array $map;

    public function __construct(ObjectResolver $resolver)
    {
        $this->resolver = $resolver;

        $this->middlewares = [
            AuthMiddleware::class,
            ValidateCsrfMiddleware::class,
        ];

        $loader = $resolver->resolve(FileLoader::class);
        $loader->load(App::$app->getConfig('app.middlewares.dir'));

        $this->addMiddlewares($loader->classNames());
    }

    public function mapMany(array $names): array
    {
        $middlewares = [];

        foreach ($names as $name) {
            $middlewares[] = $this->map($name);
        }

        return $middlewares;
    }

    public function map(string $name): IMiddleware
    {
        $map = $this->getMap();

        if (!array_key_exists($name, $map)) {
            throw new MiddlewareNotFoundException($name);
        }

        return $map[$name];
    }

    public function getMap(): array
    {
        if (isset($this->map)) {
            return $this->map;
        }

        $this->map = $this->createMap();

        return $this->map;
    }

    protected function addMiddlewares(array $middlewares): void
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
    }

    protected function createMap(): array
    {
        $middlewares = [];

        foreach ($this->middlewares as $middleware) {
            if (!in_array(IMiddleware::class, class_implements($middleware))) {
                throw new InterfaceNotFoundException($middleware, IMiddleware::class);
            }

            $middleware = $this->resolver->resolve($middleware);

            $middlewares[$middleware->getName()] = $middleware;
            $middlewares[get_class($middleware)] = $middleware;
        }

        return $middlewares;
    }
}