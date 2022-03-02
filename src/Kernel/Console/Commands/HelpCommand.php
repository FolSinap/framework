<?php

namespace FW\Kernel\Console\Commands;

use FW\Kernel\Console\CommandRouter;
use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\MessageBuilder;
use FW\Kernel\Console\Output\Output;
use function Symfony\Component\VarDumper\Dumper\esc;

class HelpCommand extends Command
{
    public function __construct(
        protected CommandRouter $router
    ) {
    }

    public function getName(): string
    {
        return 'help';
    }

    public function getDescription(): string
    {
        return 'List all available commands with description';
    }

    public function execute(Input $input, Output $output): void
    {
        $map = $this->router->getMap();

        $simple = [];
        $complicated = [];

        foreach ($map as $name => $command) {
            if (str_contains($name, ':')) {
                $prefix = array_first(explode(':', $name));
                $complicated[$prefix][$name] = $command;
            } else {
                $simple[$name] = $command;
            }
        }

        ksort($complicated);

        $message = MessageBuilder::getBuilder()
            ->tab()->writeln("COMMANDS:")
            ->tab()->foreach($simple, function (string $name, ICommand $command) {
                $spaces = 20 - strlen($name);

                return MessageBuilder::getBuilder()
                    ->startGreen()->type($name)->closeColor()
                    ->space($spaces)
                    ->startBlue()->type($command->getDescription())->closeColor()
                    ->nextLine();
            })
            ->skipLines()
            ->foreach($complicated, function (string $prefix, array $commands) {
                return MessageBuilder::getBuilder()
                    ->startYellow()->write($prefix)->closeColor()
                    ->nextLine()->tab(2)
                    ->foreach($commands, function (string $name, ICommand $command) {
                        $spaces = 20 - strlen($name);

                        return MessageBuilder::getBuilder()
                            ->startGreen()->type($name)->closeColor()
                            ->space($spaces)
                            ->startBlue()->type($command->getDescription())->closeColor()
                            ->nextLine();
                    })->nextLine();
            })
            ->getMessage();

        $output->print($message);
    }
}
