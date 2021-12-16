<?php

namespace Fwt\Framework\Kernel\Exceptions\Console;

use Fwt\Framework\Kernel\Console\Commands\Command;
use LogicException;
use Throwable;

class InvalidCommand extends LogicException
{
    public function __construct(string $class, $code = 500, Throwable $previous = null)
    {
        $message = "Invalid command $class. All commands should implement " . Command::class . ' interface.';

        parent::__construct($message, $code, $previous);
    }
}
