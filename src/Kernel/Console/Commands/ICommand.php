<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;

interface ICommand
{
    public function getName(): string;

    public function getDescription(): string;

    public function getOptions(): array;

    public function getRequiredParameters(): array;

    public function getOptionalParameters(): array;

    public function execute(Input $input, Output $output): void;
}
