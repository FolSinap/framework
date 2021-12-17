<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\Console\Commands\Command;
use Fwt\Framework\Kernel\Console\Commands\CommandWrapper;
use Fwt\Framework\Kernel\Console\Commands\MigrationCommand;
use Fwt\Framework\Kernel\Exceptions\Console\CommandNotFoundException;
use Fwt\Framework\Kernel\Exceptions\Console\InvalidCommand;
use Fwt\Framework\Kernel\ObjectResolver;

class CommandRouter
{
    protected ObjectResolver $resolver;
    protected array $commands;

    public function __construct(ObjectResolver $resolver)
    {
        $this->resolver = $resolver;

        $this->commands = [
            MigrationCommand::class,
        ];
    }

    public function map(string $name): Command
    {
        $map = $this->createMap();

        if (!array_key_exists($name, $map)) {
            throw new CommandNotFoundException($name);
        }

        return CommandWrapper::wrap($map[$name]);
    }

    public function addCommands(array $commands): void
    {
        $this->commands = array_merge($this->commands, $commands);
    }

    protected function createMap(): array
    {
        $commands = [];

        foreach ($this->commands as $command) {
            if (!in_array(Command::class, class_implements($command))) {
                throw new InvalidCommand($command);
            }

            $command = $this->resolver->resolve($command);

            $commands[$command->getName()] = $command;
        }

        return $commands;
    }
}
