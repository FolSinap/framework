<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\Container;
use Fwt\Framework\Kernel\Exceptions\Console\InvalidInputException;

class Input extends Container
{
    protected const SHORT = 'short_options';
    protected const LONG = 'long_options';
    protected const PARAMS = 'parameters';
    protected const SCRIPT = 'script';
    protected const COMMAND_NAME = 'command';

    protected function __construct(array $argv)
    {
        $data = $this->parseInput($argv);

        parent::__construct($data);
    }

    public function getFullOptions(): array
    {
        return $this->data[self::LONG];
    }

    public function getShortOptions(): array
    {
        return $this->data[self::SHORT];
    }

    public function getParameters(): array
    {
        return $this->data[self::PARAMS];
    }

    public function getCommandName(): string
    {
        return $this->data[self::COMMAND_NAME];
    }

    protected function parseInput(array $argv): array
    {
        $data[self::SCRIPT] = array_shift($argv);
        $data[self::COMMAND_NAME] = array_shift($argv);

        foreach ($argv as $parameter) {
            if (str_starts_with($parameter, '--')) {
                $parameter = ltrim($parameter, '--');

                [$key, $value] = $this->defineKeyValue($parameter);

                $data[self::LONG][$key] = $value;
            } elseif (str_starts_with($parameter, '-')) {
                $parameter = ltrim($parameter, '-');

                [$key, $value] = $this->defineKeyValue($parameter);

                foreach (str_split($key) as $char) {
                    $data[self::SHORT][$char] = $value;
                }
            } else {
                $data[self::PARAMS][] = $parameter;
            }
        }

        return $data;
    }

    private function defineKeyValue(string $string): array
    {
        $explode = explode('=', $string);

        if (count($explode) > 2) {
            throw InvalidInputException::illegalOptionCharacter('=');
        } elseif (count($explode) === 1) {
            return [$explode[0], true];
        } else {
            return [$explode[0], $explode[1]];
        }
    }
}
