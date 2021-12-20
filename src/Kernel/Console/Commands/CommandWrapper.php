<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;
use Fwt\Framework\Kernel\Exceptions\Console\InvalidInputException;

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
        $required = $this->getRequiredParams();
        $full = array_keys($input->getFullOptions());
        $short = array_keys($input->getShortOptions());

        if (in_array('help', $full)) {
            $this->showHelp($input, $output);

            return;
        }

        foreach ($required as $param => $data) {
            if (in_array($param, $full) || in_array($data[1], $short)) {
                continue;
            }

            throw InvalidInputException::requiredParameterNotProvided($param);
        }

        $this->command->execute($input, $output);
    }

    protected function showHelp(Input $input, Output $output): void
    {
        //todo: write help

        $output->info('Render help for ' . $this->getName());
    }
}
