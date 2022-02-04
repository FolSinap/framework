<?php

namespace FW\Kernel\Exceptions;

use DomainException;
use Throwable;

class IllegalTypeException extends DomainException
{
    public function __construct($value, array $types, $code = 500, Throwable $previous = null)
    {
        $type = gettype($value);
        $types = implode(', ', $types);

        $message = "Type must be one of: $types. \nGot $type instead.";

        parent::__construct($message, $code, $previous);
    }
}
