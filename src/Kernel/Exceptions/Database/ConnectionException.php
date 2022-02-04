<?php

namespace FW\Kernel\Exceptions\Database;

use LogicException;
use Throwable;

class ConnectionException extends LogicException
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
