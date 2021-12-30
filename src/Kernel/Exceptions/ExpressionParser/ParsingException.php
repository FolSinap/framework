<?php

namespace Fwt\Framework\Kernel\Exceptions\ExpressionParser;

use ParseError;

class ParsingException extends ParseError
{
    public static function invalidArrayDefinition(string $message): self
    {
        return new self($message);
    }
}
