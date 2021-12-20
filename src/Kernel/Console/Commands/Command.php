<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;

interface Command
{
    public function getName(): string;

    public function getDescription(): string;

    public function getRequiredOptions(): array;

    public function getOptionalOptions(): array;

    public function getParameters(): array;

    public function execute(Input $input, Output $output): void;
}
