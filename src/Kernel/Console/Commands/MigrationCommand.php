<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;

class MigrationCommand implements Command
{
    public function getName(): string
    {
        return 'migrate';
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function execute(Input $input, Output $output): void
    {
        // TODO: Implement execute() method.
    }
}
