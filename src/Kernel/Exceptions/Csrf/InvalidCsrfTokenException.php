<?php

namespace Fwt\Framework\Kernel\Exceptions\Csrf;

class InvalidCsrfTokenException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Invalid CSRF Token.");
    }
}
