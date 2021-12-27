<?php

namespace Fwt\Framework\Kernel\Exceptions\ExpressionParser;

use LogicException;

class ForeachExpressionException extends LogicException
{
    public static function tooManyVarsInDefinition(): self
    {
        return new self("Syntax error in foreach expression: expression should be 'key, value' or 'value'");
    }

    public static function notIterable(): self
    {
        return new self("Variable in foreach expression must be iterable");
    }

    public static function mustContainIn(): self
    {
        return new self("Foreach must contain one 'in' statement");
    }
}
