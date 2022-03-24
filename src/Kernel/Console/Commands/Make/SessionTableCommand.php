<?php

namespace FW\Kernel\Console\Commands\Make;

use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\Output;

class SessionTableCommand extends MakeMigrationCommand
{
    protected const DEFAULT_NAME = 'create_sessions_table';

    public function getName(): string
    {
        return 'session:table';
    }

    public function getDescription(): string
    {
        return 'Create migration for sessions table';
    }

    public function getRequiredParameters(): array
    {
        return [];
    }

    public function getOptionalParameters(): array
    {
        return [
            'name' => 'Override default name for migration',
        ];
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/session_migration.stub';
    }

    protected function make(Input $input, Output $output): void
    {
        $name = $this->normalizeMigrationName($this->getParameters($input)['name'] ?? static::DEFAULT_NAME);

        $this->stubReplacements = [
            'className' => $name,
            'namespace' => ltrim(config('app.migrations.namespace'), '\\'),
        ];

        $this->fileName = "$name.php";

        $this->successful = 'New migration has been successfully created.';
    }
}
