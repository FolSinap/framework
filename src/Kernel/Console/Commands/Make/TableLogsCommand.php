<?php

namespace FW\Kernel\Console\Commands\Make;

use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\Output;

class TableLogsCommand extends TableSessionCommand
{
    protected const DEFAULT_NAME = 'create_logs_table';

    public function getName(): string
    {
        return 'table:logs';
    }

    public function getDescription(): string
    {
        return 'Create migration for logs table';
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/logs_migration.stub';
    }
}
