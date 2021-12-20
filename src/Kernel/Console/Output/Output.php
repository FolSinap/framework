<?php

namespace Fwt\Framework\Kernel\Console\Output;

class Output
{
    protected const YES = ['yes', 'y'];
    protected const NEXT_LINE = "\n";

    public function input(string $message)
    {
        $this->print($message);

        return trim(fgets(STDIN));
    }

    public function confirm(string $message): bool
    {
        $answer = $this->input($message . ' [y/n] (n): ');

        if (in_array(strtolower($answer), self::YES)) {
            return true;
        }

        return false;
    }

    public function success(string $message): void
    {
        $this->print(
            MessageBuilder::getBuilder()
                ->startBlack()->onGreen()
                ->type($message)
                ->closeColor()
                ->nextLine()
                ->getMessage()
        );
    }

    public function info(string $message): void
    {
        $this->print(
            MessageBuilder::getBuilder()
                ->startBlue()
                ->type($message)
                ->closeColor()
                ->nextLine()
                ->getMessage()
        );
    }

    public function error(string $message): void
    {
        $this->print(
            MessageBuilder::getBuilder()
                ->onRed()
                ->type($message)
                ->closeColor()
                ->nextLine()
                ->getMessage()
        );
    }

    public function warning(string $message): void
    {
        $this->print(
            MessageBuilder::getBuilder()
                ->startBlack()->onYellow()
                ->type($message)
                ->closeColor()
                ->nextLine()
                ->getMessage()
        );
    }

    public function lines(array $messages)
    {
        foreach ($messages as $message) {
            $this->line($message);
        }
    }

    public function line(string $message = ''): void
    {
        $this->print($message . self::NEXT_LINE);
    }

    public function print(string $message): void
    {
        print $message;
    }
}
