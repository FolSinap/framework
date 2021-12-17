<?php

namespace Fwt\Framework\Kernel\Exceptions\Console;

use Throwable;
use UnexpectedValueException;

class InvalidInputException extends UnexpectedValueException
{
    public static function illegalOptionCharacter(string $char, int $code = 500, Throwable $previous = null): self
    {
        $message = "Input options shouldn't contain $char.";

        return new self($message);
    }
}
