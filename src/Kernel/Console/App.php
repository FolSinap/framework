<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\App as BaseApp;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Exceptions\Console\CommandNotFoundException;
use Fwt\Framework\Kernel\Exceptions\Console\InvalidInputException;
use Fwt\Framework\Kernel\ObjectResolver;

class App extends BaseApp
{
    public array $argv;
    public int $argc;
    protected int $terminalWidth;

    public function __construct(string $projectDir, array $argv, int $argc)
    {
        $this->argv = $argv;
        $this->argc = $argc;
        $this->findOutTerminalWidth();

        parent::__construct($projectDir);
    }

    public function run(): void
    {
        try {
            $command = isset($this->argv[1])
                ? $this->getCommandRouter()->map($this->getInput()->getCommandName())
                : $this->getCommandRouter()->map('help');

            $dependencies = $this->container[ObjectResolver::class]->resolveDependencies(get_class($command), 'execute');

            $command->execute(...$dependencies);
        } catch (CommandNotFoundException|InvalidInputException $exception) {
            (new Output())->error($exception->getMessage());
        }
    }

    public function getCommandRouter(): CommandRouter
    {
        return $this->getContainer()->get(CommandRouter::class);
    }

    public function getInput(): Input
    {
        return $this->getContainer()->get(Input::class);
    }

    public function getTerminalWidth(): int
    {
        return $this->terminalWidth;
    }

    protected function bootContainer(): void
    {
        parent::bootContainer();

        $this->container[Input::class] = Input::getInstance($this->argv);
        $this->container[CommandRouter::class] = new CommandRouter($this->container[ObjectResolver::class]);
    }

    protected function findOutTerminalWidth(): void
    {
        if (strtoupper(php_uname('s')) === 'WINDOWS') {
            //this require some testing
            exec('mode con', $width);

            $width = filter_var($width[4], FILTER_SANITIZE_NUMBER_INT);
        } else {
            exec('tput cols', $width);

            $width = $width[0];
        }

        $this->terminalWidth = (int) $width;
    }
}
