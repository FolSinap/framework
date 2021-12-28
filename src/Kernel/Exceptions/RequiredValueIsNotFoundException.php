<?php

namespace Fwt\Framework\Kernel\Exceptions;

use DomainException;
use Throwable;

class RequiredValueIsNotFoundException extends DomainException
{
    public function __construct(string $key, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Required value $key is not set.", $code, $previous);
    }
}
