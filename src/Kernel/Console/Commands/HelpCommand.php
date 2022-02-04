<?php

namespace FW\Kernel\Console\Commands;

use FW\Kernel\Console\CommandRouter;
use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\MessageBuilder;
use FW\Kernel\Console\Output\Output;

class HelpCommand extends Command
{
    protected CommandRouter $router;

    public function __construct(CommandRouter $router)
    {
        $this->router = $router;
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

        $message = MessageBuilder::getBuilder()
            ->tab()->writeln("COMMANDS:")
            ->tab()->foreach($map, function ($key, $command) {
                $spaces = 20 - strlen($command->getName());

                return MessageBuilder::getBuilder()
                    ->startGreen()->type($command->getName())->closeColor()
                    ->space($spaces)
                    ->startBlue()->type($command->getDescription())->closeColor()
                    ->nextLine();
            })
            ->getMessage();

        $output->print($message);
    }
}
