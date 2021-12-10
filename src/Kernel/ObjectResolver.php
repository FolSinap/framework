<?php

namespace Fwt\Framework\Kernel;

use BadMethodCallException;
use Fwt\Framework\Kernel\Exceptions\Resolver\UndefinedParameterException;
use ReflectionClass;
use ReflectionException;

class ObjectResolver
{
    public function resolve(string $class): object
    {
        if (App::$app->getContainer()->exists($class)) {
            return App::$app->getContainer()->get($class);
        }

        $parameters = $this->resolveDependencies($class);

        return new $class(...$parameters);
    }

    public function resolveDependencies(string $class, string $method = null): array
    {
        $reflection = new ReflectionClass($class);
        $parameters = [];

        try {
            $method = is_null($method) ? $reflection->getConstructor() : $reflection->getMethod($method);
        } catch (ReflectionException $exception) {
            throw new BadMethodCallException("$method doesn't exist in $class", 500);
        }

        if ($method) {
            foreach ($method->getParameters() as $parameter) {
                $dependencyClass = $parameter->getClass();

                if (is_null($dependencyClass)) {
                    throw new UndefinedParameterException($parameter, $method, $reflection);
                }

                $parameters[] = $this->resolve($dependencyClass->getName());
            }
        }

        return $parameters;
    }
}
