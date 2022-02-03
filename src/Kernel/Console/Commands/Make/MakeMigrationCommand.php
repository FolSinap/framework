<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\FileLoader;

class MakeMigrationCommand extends MakeCommand
{
    protected FileLoader $loader;

    public function __construct(FileLoader $loader)
    {
        $this->loader = $loader;
    }

    public function getName(): string
    {
        return 'make:migration';
    }

    public function getDescription(): string
    {
        return 'Create new migration file.';
    }

    public function getRequiredParameters(): array
    {
        return [
            'name' => 'Name of migration class.',
        ];
    }

    protected function make(Input $input, Output $output): void
    {
        $name = $this->normalizeMigrationName($this->getParameters($input)['name']);

        $this->stubReplacements = [
            'className' => $name,
            'namespace' => ltrim(config('app.migrations.namespace'), '\\'),
        ];

        $this->fileName = "$name.php";

        $this->successful = 'New migration has been successfully created.';
    }

    protected function normalizeMigrationName(string $name): string
    {
        $this->loader->load($this->getBaseDir());

        $numbers = [];

        foreach ($this->loader->baseNames() as $migration) {
            $migrationName = str_replace('.php', '', $migration);

            preg_match('/m(\d{4})_/', $migrationName, $matches);
            $numbers[] = (int) $matches[1];
        }

        $numbers = empty($numbers) ? [0] : $numbers;

        $nextNumber = str_pad((max($numbers) + 1), 4, '0', STR_PAD_LEFT);

        return "m$nextNumber" . "_$name";
    }

    protected function getBaseDir(): string
    {
        return config('app.migrations.dir');
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/migration.stub';
    }
}
