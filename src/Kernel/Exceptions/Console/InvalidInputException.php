<?php

namespace Fwt\Framework\Kernel\Exceptions\Console;

use Throwable;
use UnexpectedValueException;

class InvalidInputException extends UnexpectedValueException
{
    public static function illegalOptionCharacter(string $char, int $code = 500, Throwable $previous = null): self
    {
        $message = "Input options shouldn't contain $char.";

        return new self($message, $code, $previous);
    }

    public static function notEnoughParameters(int $expected, int $actual, int $code = 500, Throwable $previous = null): self
    {
        $message = "Expected at least $expected parameter" . ($expected > 1 ? 's' : '') . ", but got $actual.";

        return new self($message, $code, $previous);
    }

    public static function tooManyParameters(int $code = 500, Throwable $previous = null): self
    {
        $message = "Expected less parameters.";

        return new self($message, $code, $previous);
    }
}
