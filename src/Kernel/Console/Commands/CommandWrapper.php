<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\MessageBuilder;
use Fwt\Framework\Kernel\Console\Output\Output;
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

    public function getDescription(): string
    {
        return $this->command->getDescription();
    }

    public function getOptions(): array
    {
        return array_merge($this->command->getOptions(), [
            'help' => ['Show help for the command'],
        ]);
    }

    public function getOptionalParameters(): array
    {
        return $this->command->getOptionalParameters();
    }

    public function getRequiredParameters(): array
    {
        return $this->command->getRequiredParameters();
    }

    public function execute(Input $input, Output $output): void
    {
        $requiredParameters = $this->getRequiredParameters();
        $full = array_keys($input->getFullOptions());
        $params = $input->getParameters();

        if (in_array('help', $full)) {
            $this->showHelp($input, $output);

            return;
        }

        if (count($params) < count($requiredParameters)) {
            throw InvalidInputException::notEnoughParameters(count($requiredParameters), count($params));
        }

        if (count($params) > count(array_merge($requiredParameters, $this->getOptionalParameters()))) {
            throw InvalidInputException::tooManyParameters();
        }

        $this->command->execute($input, $output);
    }

    protected function showHelp(Input $input, Output $output): void
    {
        $script = $input->getScriptName();
        $command = $input->getCommandName();
        $options = $this->getOptions();
        $paramNames = [];
        $params = [];

        foreach ($this->getRequiredParameters() as $name => $description) {
            $paramNames[] = "<$name>";
            $params[$name] = $description;
        }

        foreach ($this->getOptionalParameters() as $name => $description) {
            $paramNames[] = "<?$name>";
            $params[$name] = $description;
        }

        $paramNames = implode(', ', $paramNames);

        $output->print(
            MessageBuilder::getBuilder()
                ->skipLines()
                ->tab()->writeln("php $script $command $paramNames [OPTIONS]")
                ->tab()->writeln(MessageBuilder::getBuilder()->green($this->getDescription()))
                ->dropTab()
                ->if(!empty($options),
                    MessageBuilder::getBuilder()
                        ->skipLines()
                        ->tab()->writeln("OPTIONS:")
                        ->tab()->foreach($options, function ($name, $data) {
                            $description = $data[0];
                            $short = $data[1] ?? null;
                            $definition = "--$name" . ($short ? ", -$short" : '');
                            $spaces = 20 - strlen($definition);

                            return MessageBuilder::getBuilder()
                                ->write($definition)
                                ->space($spaces)
                                ->blue($description)->nextLine();
                        })
                )
                ->if(!empty($params),
                    MessageBuilder::getBuilder()
                        ->skipLines()
                        ->tab()->writeln("PARAMETERS:")
                        ->tab()->foreach($params, function ($name, $description) {
                            $spaces = 20 - strlen($name);

                            return MessageBuilder::getBuilder()->write("$name")
                                ->space($spaces)
                                ->blue($description)
                                ->nextLine();
                        })
                )
        );
    }
}
