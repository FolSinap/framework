<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;

class MakeMigrationCommand extends MakeCommand
{
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
            'name' => ['Name of migration class.'],
        ];
    }

    public function execute(Input $input, Output $output): void
    {
        $migrations = scandir($this->getBaseDir());
        $numbers = [];

        foreach ($migrations as $migration) {
            if (in_array($migration, ['.', '..'])) {
                continue;
            }

            $migrationName = rtrim($migration, '.php');

            preg_match('/m(\d{4})_/', $migrationName, $matches);
            $numbers[] = (int) $matches[1];
        }

        $numbers = empty($numbers) ? [0] : $numbers;

        $nextNumber = str_pad((max($numbers) + 1), 4, '0', STR_PAD_LEFT);

        $name = $input->getParameters()[0];
        $name = "m$nextNumber" . "_$name";

        $stub = $this->replaceStubTemplates([
            'class_name' => $name,
            'namespace' => ltrim(App::$app->getConfig('app.migrations.namespace'), '\\'),
        ]);

        if ($this->createFile("$name.php", $stub)) {
            $output->success('New migration is created successfully!');
        } else {
            $output->error('Something went wrong.');
        }
    }

    protected function getBaseDir(): string
    {
        return App::$app->getConfig('app.migrations.dir');
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/migration.stub';
    }
}
