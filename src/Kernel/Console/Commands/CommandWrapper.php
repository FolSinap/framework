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

    public function getRequiredOptions(): array
    {
        return $this->command->getRequiredOptions();
    }

    public function getOptionalOptions(): array
    {
        return array_merge($this->command->getOptionalOptions(), [
            'help' => ['Show help for the command'],
        ]);
    }

    public function getParameters(): array
    {
        return $this->command->getParameters();
    }

    public function execute(Input $input, Output $output): void
    {
        $required = $this->getRequiredOptions();
        $full = array_keys($input->getFullOptions());
        $short = array_keys($input->getShortOptions());
        $params = $input->getParameters();

        if (in_array('help', $full)) {
            $this->showHelp($input, $output);

            return;
        }

        if (count($params) > count($this->getParameters())) {
            throw InvalidInputException::tooManyParameters();
        }

        foreach ($required as $option => $data) {
            if (in_array($option, $full) || in_array($data[1], $short)) {
                continue;
            }

            throw InvalidInputException::requiredOptionNotProvided($option);
        }

        $this->command->execute($input, $output);
    }

    protected function showHelp(Input $input, Output $output): void
    {
        $script = $input->getScriptName();
        $command = $input->getCommandName();
        $params = $this->getParameters();
        $paramNames = implode(', ', array_keys($params));
        $paramNames = $paramNames === '' ? '' : "<?$paramNames>";
        $required = $this->getRequiredOptions();
        $optional = $this->getOptionalOptions();

        $messageBuilder = MessageBuilder::getBuilder();

        $messageBuilder
            ->skipLines()
            ->tab()->writeln("php $script $command $paramNames [OPTIONS]")
            ->tab()->writeln(MessageBuilder::getBuilder()->green($this->getDescription()))
            ->dropTab()
            ->if(!empty($required),
                MessageBuilder::getBuilder()
                    ->skipLines()
                    ->tab()->writeln("REQUIRED OPTIONS:")
                    ->tab()->foreach($required, function ($name, $data) {
                        $description = $data[0];
                        $short = $data[1] ?? null;
                        $definition = "--$name" . ($short ? ", -$short" : '');
                        $spaces = 20 - strlen($definition);

                        return MessageBuilder::getBuilder()->write($definition)
                            ->space($spaces)
                            ->blue($description);
                    })
                    ->skipLines(2)
            )
            ->if(!empty($optional),
                MessageBuilder::getBuilder()
                    ->skipLines()
                    ->tab()->writeln("OPTIONAL OPTIONS:")
                    ->tab()->foreach($optional, function ($name, $data) {
                        $description = $data[0];
                        $short = $data[1] ?? null;
                        $definition = "--$name" . ($short ? ", -$short" : '');
                        $spaces = 20 - strlen($definition);

                        return MessageBuilder::getBuilder()->write($definition)
                            ->space($spaces)
                            ->blue($description)
                            ->skipLines();
                    })
            )
            ->if(!empty($params),
                MessageBuilder::getBuilder()
                    ->skipLines()
                    ->tab()->writeln("PARAMETERS:")
                    ->tab()->foreach($params, function ($name, $data) {
                        $description = $data[0];
                        $spaces = 20 - strlen($name);

                        return MessageBuilder::getBuilder()->write("$name")
                            ->space($spaces)
                            ->blue($description)
                            ->skipLines();
                    })
            )
        ;

        $output->print($messageBuilder);
    }
}
