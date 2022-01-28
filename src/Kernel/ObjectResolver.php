<?php

namespace Fwt\Framework\Kernel;

use BadMethodCallException;
use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Exceptions\Resolver\UndefinedParameterException;
use ReflectionClass;
use ReflectionException;

class ObjectResolver
{
    protected FileConfig $presetDependencies;

    public function __construct()
    {
        $this->presetDependencies = App::$app->getConfig('dependencies');
    }

    public function resolve(string $class): object
    {
        if (App::$app->getContainer()->exists($class)) {
            return App::$app->getContainer()->get($class);
        }

        $parameters = $this->resolveDependencies($class);

        return new $class(...$parameters);
    }

    public function resolveDependencies(string $class, string $method = null, array $preset = []): array
    {
        $reflection = new ReflectionClass($class);
        $parameters = [];

        try {
            $method = is_null($method) ? $reflection->getConstructor() : $reflection->getMethod($method);
        } catch (ReflectionException $exception) {
            throw new BadMethodCallException("$method doesn't exist in $class", 500);
        }

        if ($method) {
            $configPreset = $method->isConstructor() ? $this->getPresetDependencies($class) : [];
            $preset = array_merge($configPreset, $preset);

            foreach ($method->getParameters() as $parameter) {
                $dependencyClass = $parameter->getClass();

                if (array_key_exists($parameter->name, $preset)) {
                    if (!is_null($dependencyClass) && is_subclass_of($dependencyClass->name, Model::class)) {
                        $parameters[] = $dependencyClass->name::find($preset[$parameter->name]);
                    } else {
                        $parameters[] = $preset[$parameter->name];
                    }

                    continue;
                }

                if (is_null($dependencyClass)) {
                    throw new UndefinedParameterException($parameter, $method, $reflection);
                }

                $parameters[] = $this->resolve($dependencyClass->getName());
            }
        }

        return $parameters;
    }

    protected function getPresetDependencies(string $class): array
    {
        return $this->presetDependencies[$class] ?? [];
    }
}
