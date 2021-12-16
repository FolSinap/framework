<?php

namespace Fwt\Framework\Kernel\Exceptions\Console;

use OutOfBoundsException;
use Throwable;

class CommandNotFoundException extends OutOfBoundsException
{
    public function __construct(string $command, $code = 500, Throwable $previous = null)
    {
        $message = "Command $command was not found";

        parent::__construct($message, $code, $previous);
    }
}
