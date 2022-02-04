<?php

namespace FW\Kernel\Exceptions\Router;

use OutOfRangeException;
use Throwable;

class UnknownRouteNameException extends OutOfRangeException
{
    public function __construct(string $routeName, int $code = 500, Throwable $previous = null)
    {
        $message = "Route with name '$routeName' doesn't exist.";

        parent::__construct($message, $code, $previous);
    }
}
