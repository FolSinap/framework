<?php

namespace FW\Kernel\Exceptions\Guards;

use OutOfBoundsException;
use Throwable;

class GuardNotFoundException extends OutOfBoundsException
{
    public function __construct(string $guard, $code = 500, Throwable $previous = null)
    {
        $message = "Guard $guard was not found";

        parent::__construct($message, $code, $previous);
    }
}
