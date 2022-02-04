<?php

namespace FW\Kernel\Console;

use FW\Kernel\Console\Commands\ICommand;
use FW\Kernel\Exceptions\Console\CommandNotFoundException;
use FW\Kernel\Exceptions\InterfaceNotFoundException;
use FW\Kernel\FileLoader;
use FW\Kernel\ObjectResolver;

class CommandRouter
{
    protected ObjectResolver $resolver;
    protected array $commands;
    protected array $map;

    public function __construct(ObjectResolver $resolver)
    {
        $this->resolver = $resolver;

        $loader = new FileLoader();
        $loader->allowedExtensions(['php'])->ignoreHidden();

        $loader->load(__DIR__ . '/Commands');
        $loader->loadIfExists(config('app.commands.dir'));

        $this->commands = $loader->concreteClasses();
    }

    public function map(string $name): ICommand
    {
        $map = $this->getMap();

        if (!array_key_exists($name, $map)) {
            throw new CommandNotFoundException($name);
        }

        return CommandWrapper::wrap($map[$name]);
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
        $commands = [];

        foreach ($this->commands as $command) {
            if (!in_array(ICommand::class, class_implements($command))) {
                throw new InterfaceNotFoundException($command, ICommand::class);
            }

            $command = $this->resolver->resolve($command);

            $commands[$command->getName()] = $command;
        }

        return $commands;
    }
}
