<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\Console\Commands\Command;
use Fwt\Framework\Kernel\Console\Commands\CommandWrapper;
use Fwt\Framework\Kernel\Console\Commands\HelpCommand;
use Fwt\Framework\Kernel\Console\Commands\Make\MakeMigrationCommand;
use Fwt\Framework\Kernel\Console\Commands\Make\MakeModelCommand;
use Fwt\Framework\Kernel\Console\Commands\MigrationCommand;
use Fwt\Framework\Kernel\Exceptions\Console\CommandNotFoundException;
use Fwt\Framework\Kernel\Exceptions\InterfaceNotFoundException;
use Fwt\Framework\Kernel\ObjectResolver;

class CommandRouter
{
    protected ObjectResolver $resolver;
    protected array $commands;
    protected array $map;

    public function __construct(ObjectResolver $resolver)
    {
        $this->resolver = $resolver;

        $this->commands = [
            HelpCommand::class,
            MigrationCommand::class,
            MakeMigrationCommand::class,
            MakeModelCommand::class,
        ];

        $this->addCommands(App::$app->getConfig('console.commands', []));
    }

    public function map(string $name): Command
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

    protected function addCommands(array $commands): void
    {
        $this->commands = array_merge($this->commands, $commands);
    }

    protected function createMap(): array
    {
        $commands = [];

        foreach ($this->commands as $command) {
            if (!in_array(Command::class, class_implements($command))) {
                throw new InterfaceNotFoundException($command, Command::class);
            }

            $command = $this->resolver->resolve($command);

            $commands[$command->getName()] = $command;
        }

        return $commands;
    }
}
