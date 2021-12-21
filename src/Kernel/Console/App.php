<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\App as BaseApp;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Container;
use Fwt\Framework\Kernel\Database\Connection;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Exceptions\Console\CommandNotFoundException;
use Fwt\Framework\Kernel\Exceptions\Console\InvalidInputException;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\Router;

class App extends BaseApp
{
    public array $argv;
    public int $argc;

    public function __construct(string $projectDir, array $argv, int $argc)
    {
        $this->argv = $argv;
        $this->argc = $argc;

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

    protected function bootContainer(): void
    {
        $this->container = Container::getInstance();

        $resolver = $this->container[ObjectResolver::class] = new ObjectResolver();
        $this->container[Router::class] = Router::getRouter($resolver);
        $this->container[Input::class] = Input::getInstance($this->argv);
        $this->container[CommandRouter::class] = new CommandRouter($this->container[ObjectResolver::class]);

        $this->container[Connection::class] = new Connection(
            getenv('DB'),
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $this->container[Database::class] = new Database($this->container[Connection::class]);
    }
}
