<?php

namespace FW\Kernel\Exceptions\ExpressionParser;

use InvalidArgumentException;
use Throwable;

class VariableParsingException extends InvalidArgumentException
{
    public function __construct(string $varName, string $type, $code = 0, Throwable $previous = null)
    {
        $message = "$varName is not $type";
        parent::__construct($message, $code, $previous);
    }
}
