<?php

namespace FW\Kernel\Exceptions\Login;

use RuntimeException;
use Throwable;

class LoginException extends RuntimeException
{
    public static function incorrectData(int $code = 401, Throwable $previous = null): self
    {
        return new self("Incorrect data to log in.", $code, $previous);
    }
}
