<?php

namespace FW\Kernel\Exceptions\ExpressionParser;

use RangeException;
use Throwable;

class UndefinedKeyException extends RangeException
{
    public function __construct($key, $code = 0, Throwable $previous = null)
    {
        $message = "Undefined key $key.";
        parent::__construct($message, $code, $previous);
    }
}
