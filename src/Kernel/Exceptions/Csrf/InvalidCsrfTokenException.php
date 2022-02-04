<?php

namespace FW\Kernel\Exceptions\Csrf;

use RuntimeException;

class InvalidCsrfTokenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Invalid CSRF Token.");
    }
}
