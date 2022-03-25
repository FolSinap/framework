<?php

namespace FW\Kernel\Logging;

use Monolog\Handler\HandlerInterface;

class HandlerFactory
{
    public function __construct(
        protected array $handlers,
    ) {
    }

    public function create(): ?HandlerInterface
    {

    }
}
