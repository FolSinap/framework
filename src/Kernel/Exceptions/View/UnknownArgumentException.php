<?php

namespace FW\Kernel\Exceptions\View;

use LogicException;
use Throwable;

class UnknownArgumentException extends LogicException
{
    public function __construct(string $arg, $code = 500, Throwable $previous = null)
    {
        $message = 'Unknown argument ' . $arg;

        parent::__construct($message, $code, $previous);
    }
}