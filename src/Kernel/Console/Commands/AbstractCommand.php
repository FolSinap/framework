<?php

namespace Fwt\Framework\Kernel\Console\Commands;

abstract class AbstractCommand implements Command
{
    public function getRequiredOptions(): array
    {
        return [];
    }

    public function getOptionalOptions(): array
    {
        return [];
    }

    public function getParameters(): array
    {
        return [];
    }
}
