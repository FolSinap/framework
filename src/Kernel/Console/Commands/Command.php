<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;

interface Command
{
    public function getName(): string;

    public function getRequiredParams(): array;

    public function execute(Input $input, Output $output): void;
}
