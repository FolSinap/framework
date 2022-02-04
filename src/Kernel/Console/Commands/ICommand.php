<?php

namespace FW\Kernel\Console\Commands;

use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\Output;

interface ICommand
{
    public function getName(): string;

    public function getDescription(): string;

    public function getOptions(): array;

    public function getRequiredParameters(): array;

    public function getOptionalParameters(): array;

    public function execute(Input $input, Output $output): void;
}
