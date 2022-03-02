<?php

namespace FW\Kernel\Guards;

use FW\Kernel\Exceptions\Guards\GuardNotFoundException;
use FW\Kernel\Exceptions\InvalidExtensionException;
use FW\Kernel\FileLoader;
use FW\Kernel\ObjectResolver;

class GuardMapper
{
    protected array $guards;
    protected array $map;

    public function __construct(
        protected ObjectResolver $resolver
    ) {
        $loader = new FileLoader();
        $loader->allowedExtensions(['.php'])->ignoreHidden()->loadIfExists(config('app.guards.dir'));

        $this->guards = $loader->concreteClasses();
    }

    public function map(string $name): Guard
    {
        $map = $this->getMap();

        if (!array_key_exists($name, $map)) {
            throw new GuardNotFoundException($name);
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
        $guards = [];

        foreach ($this->guards as $guardClass) {
            if (!in_array(Guard::class, class_parents($guardClass))) {
                throw new InvalidExtensionException($guardClass, Guard::class);
            }

            /** @var Guard $guard */
            $guard = $this->resolver->resolve($guardClass);

            $guards[$guard->getName()] = $guard;
            $guards[$guard::class] = $guard;
        }

        return $guards;
    }
}
