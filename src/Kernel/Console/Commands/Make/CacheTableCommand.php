<?php

namespace FW\Kernel\Console\Commands\Make;

class CacheTableCommand extends SessionTableCommand
{
    protected const DEFAULT_NAME = 'create_cache_table';

    public function getName(): string
    {
        return 'cache:table';
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
