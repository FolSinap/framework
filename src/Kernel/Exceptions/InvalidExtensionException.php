<?php

namespace FW\Kernel\Exceptions;

use LogicException;
use Throwable;

class InvalidExtensionException extends LogicException
{
    public function __construct(string $childClass, string $parentClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Class $childClass must extend $parentClass.", $code, $previous);
    }
}
