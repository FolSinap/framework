<?php

namespace Fwt\Framework\Kernel\Exceptions;

use LogicException;
use Throwable;

class InterfaceNotFoundException extends LogicException
{
    public function __construct(string $class, string $interface, $code = 500, Throwable $previous = null)
    {
        $message = "Class $class should implement $interface.";

        parent::__construct($message, $code, $previous);
    }
}
