<?php

namespace FW\Kernel\Console\Output;

use FW\Kernel\Console\TextBuilder;

class MessageBuilder extends TextBuilder
{
    use Colorable;

    protected const DEFAULT_TERMINAL_WIDTH = 80;
    protected const GRAY = "\e[90m";
    protected const BLACK = "\e[30m";
    protected const RED = "\e[31m";
    protected const GREEN = "\e[32m";
    protected const YELLOW = "\e[33m";
    protected const BLUE = "\e[34m";
    protected const WHITE = "\e[37m";

    protected const ON_BLACK = "\e[40m";
    protected const ON_RED = "\e[41m";
    protected const ON_GREEN = "\e[42m";
    protected const ON_YELLOW = "\e[43m";
    protected const ON_BLUE = "\e[44m";
    protected const ON_WHITE = "\e[47m";
    protected const CLOSE_COLOR = "\033[0m";

    protected int $terminalWidth;

    public function __construct()
    {
        $app = app();

        $this->terminalWidth = method_exists($app, 'getTerminalWidth')
            ? $app->getTerminalWidth()
            : self::DEFAULT_TERMINAL_WIDTH;
    }

    public static function getBuilder(): self
    {
        return new self();
    }

    public function drawLines(int $count = 1, string $char = '-'): self
    {
        for ($i = 0;$i < $count;$i++) {
            $this->drawLine($char);
        }

        return $this;
    }

    public function drawLine(string $char = '-'): self
    {
        $repeatCount = (int) floor($this->terminalWidth / strlen($char));
        $postfixLength = $this->terminalWidth - ($repeatCount * strlen($char));
        $postfix = substr($char, 0, $postfixLength);
        $line = str_repeat($char, $repeatCount);

        $this->type($line . $postfix)->nextLine();

        return $this;
    }

    /**
     * @param array<int> $percentage Percentage of each peace of content
     * @param array<string> $contents Array of contents
     * @param string $separator Char to separate columns
     */
    public function separate(array $percentage, array $contents, string $separator = '|'): self
    {
        if (array_sum($percentage) > 100) {
            //todo: exception
            throw new \Exception('Percentage can\'t be more than 100');
        }

        if (count($percentage) !== count($contents)) {
            //todo: exception
            throw new \Exception('Count of percentages must equal count of contents');
        }

        $percentage = array_values($percentage);
        $contents = array_values($contents);

        //subtract separators count
        $screenWidth = $this->terminalWidth - (count($percentage) - strlen($separator));
        $output = [];

        foreach ($contents as $index => $content) {
            $percent = $percentage[$index];
            $width = ($screenWidth * $percent) / 100;
            $contentLength = strlen($content);

            if ($contentLength > $width) {
                //since content might be dynamically declared we can't throw exceptions.
                //So we just pretend terminal window is bigger than it is
                $this->terminalWidth += $contentLength - $width;
                $width = $contentLength;
            }

            $spaceCount = ($width - $contentLength) / 2;
            $output[] = str_pad(self::getBuilder()->space($spaceCount)->type($content), $width);
        }

        $this->type(implode($separator, $output));

        return $this;
    }

    public function middle(string $message): self
    {
        $length = strlen($message);
        $spaceCount = ($this->terminalWidth - $length) / 2;

        $this->space($spaceCount)->type($message)->nextLine();

        return $this;
    }

    public function getMessage(): string
    {
        return $this->getText();
    }
}
