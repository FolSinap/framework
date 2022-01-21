<?php

namespace Fwt\Framework\Kernel\Console\Output;

class Output
{
    protected const YES = ['yes', 'y'];
    protected const NO = ['no', 'n'];
    protected const NEXT_LINE = "\n";

    public function input(string $message)
    {
        $this->print($message);

        return trim(fgets(STDIN));
    }

    public function confirm(string $message): bool
    {
        $answer = $this->input($message . ' [y/n] (n): ');
        $answer = $answer === '' ? 'n' : $answer;
        $answer = strtolower($answer);

        if (in_array($answer, self::YES)) {
            return true;
        } elseif (in_array($answer, self::NO)) {
            return false;
        }

        return $this->tryAgain();
    }

    /**
     * @param string $message
     * @param string[] $choices
     *
     * @return string
     */
    public function choose(string $message, array $choices): string
    {
        $choice = $this->input(
            MessageBuilder::getBuilder()
            ->type($message)
            ->foreach($choices, function ($key, $value) {
                return MessageBuilder::getBuilder()
                    ->nextLine()->tab()
                    ->startYellow()->write("[$key] ")->closeColor()
                    ->startGreen()->type($value)->closeColor();
            })
            ->nextLine()
            ->getMessage()
        );

        if (array_key_exists($choice, $choices)) {
            return $choices[$choice];
        }

        if (!in_array($choice, $choices)) {
            return $this->tryAgain();
        }

        return $choice;
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

    protected function tryAgain()
    {
        $previous = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];
        $method = $previous['function'];
        $args = $previous['args'];

        $this->line('Try again.');

        return $this->$method(...$args);
    }
}
