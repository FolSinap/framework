<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\Container;

class Input extends Container
{
    protected function __construct(array $argv)
    {
        parent::__construct($argv);
    }
}
