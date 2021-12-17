<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;

class CommandWrapper implements Command
{
    protected Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public static function wrap(Command $command): self
    {
        return new self($command);
    }

    public function getName(): string
    {
        return $this->command->getName();
    }

    public function getRequiredParams(): array
    {
        return $this->command->getRequiredParams();
    }

    public function getOptionalParams(): array
    {
        return $this->command->getOptionalParams();
    }

    public function execute(Input $input, Output $output): void
    {
        //todo: Write code

        $this->command->execute($input, $output);
    }
}
