<?php

namespace Fwt\Framework\Kernel\Exceptions\Config;

use LogicException;
use Throwable;

class ValueIsNotConfiguredException extends LogicException
{
    public function __construct(string $configKey, $code = 0, Throwable $previous = null)
    {
        parent::__construct("$configKey is not yet configured well", $code, $previous);
    }
}
