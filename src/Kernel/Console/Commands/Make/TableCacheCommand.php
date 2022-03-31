<?php

namespace FW\Kernel\Console\Commands\Make;

class TableCacheCommand extends TableSessionCommand
{
    protected const DEFAULT_NAME = 'create_cache_table';

    public function getName(): string
    {
        return 'table:cache';
    }

    public function getDescription(): string
    {
        return 'Create migration for cache table';
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/cache_migration.stub';
    }
}
