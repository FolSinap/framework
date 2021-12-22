<?php

namespace Fwt\Framework\Kernel\Exceptions\Middleware;

use OutOfBoundsException;
use Throwable;

class MiddlewareNotFoundException extends OutOfBoundsException
{
    public function __construct(string $middleware, $code = 500, Throwable $previous = null)
    {
        $message = "Middleware $middleware was not found";

        parent::__construct($message, $code, $previous);
    }
}
