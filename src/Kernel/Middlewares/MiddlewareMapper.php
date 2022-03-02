<?php

namespace FW\Kernel\Middlewares;

use FW\Kernel\Exceptions\InterfaceNotFoundException;
use FW\Kernel\Exceptions\Middleware\MiddlewareNotFoundException;
use FW\Kernel\FileLoader;
use FW\Kernel\ObjectResolver;

class MiddlewareMapper
{
    protected array $middlewares;
    protected array $map;

    public function __construct(
        protected ObjectResolver $resolver
    ) {
        $loader = new FileLoader();
        $loader->allowedExtensions(['.php'])->ignoreHidden()->except([basename(__FILE__)]);

        $loader->load(__DIR__);
        $loader->loadIfExists(config('app.middlewares.dir'));

        $this->middlewares = $loader->concreteClasses();
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

    protected function createMap(): array
    {
        $middlewares = [];

        foreach ($this->middlewares as $middleware) {
            if (!in_array(IMiddleware::class, class_implements($middleware))) {
                throw new InterfaceNotFoundException($middleware, IMiddleware::class);
            }

            $middleware = $this->resolver->resolve($middleware);

            $middlewares[$middleware->getName()] = $middleware;
            $middlewares[$middleware::class] = $middleware;
        }

        return $middlewares;
    }
}