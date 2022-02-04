<?php

namespace FW\Kernel\Console\Commands;

use Exception;
use FW\Kernel\Console\App;
use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\Output;
use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\Models\Migration;
use FW\Kernel\Database\Migration as ExecutableMigration;
use FW\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use FW\Kernel\FileLoader;
use FW\Kernel\ObjectResolver;

class MigrationCommand extends Command
{
    protected Database $database;
    protected ObjectResolver $resolver;
    protected FileLoader $loader;
    protected array $dry = [];

    public function __construct(Database $database, ObjectResolver $resolver, FileLoader $loader)
    {
        $this->database = $database;
        $this->resolver = $resolver;
        $this->loader = $loader;
    }

    public function getName(): string
    {
        return 'migrate';
    }

    public function getDescription(): string
    {
        return 'Run up or down existing migrations.';
    }

    public function getOptions(): array
    {
        return [
            'down' => ['Run down all migrations', 'd'],
            'back' => ['Rollback last migration', 'b'],
            'dry' => ['Print queries without executing it'],
        ];
    }

    public function execute(Input $input, Output $output): void
    {
        $this->createMigrationsTable();

        $executedMigrations = Migration::all();
        $executedMigrations = array_map(function ($migration) {
            return $migration->name;
        }, $executedMigrations->toArray());

        $down = $input->getOption('down', 'd');
        $back = $input->getOption('back', 'b');
        $dry = $input->getOption('dry') ?? false;

        $migrations = $this->resolveMigrationObjects();

        $numberOfExecutions = 0;

        if ($back) {
            if ($last = array_pop($executedMigrations)) {
                $this->runDown($migrations[$last], $dry);

                $numberOfExecutions++;
            }
        } else {
            if ($down) {
                krsort($migrations);
            }

            foreach ($migrations as $migration) {
                if ($down && in_array($migration->getName(), $executedMigrations)) {
                    $this->runDown($migration, $dry);

                    $numberOfExecutions++;
                } elseif (!$down && !in_array($migration->getName(), $executedMigrations)) {
                    $this->runUp($migration, $dry);

                    $numberOfExecutions++;
                }
            }
        }

        if ($dry) {
            foreach ($this->dry as $query) {
                $output->info($query);
            }
        } else {
            $output->success($numberOfExecutions . ' migrations were executed.');
        }
    }

    protected function runDown(ExecutableMigration $migration, bool $dry = false): void
    {
        $migration->down();

        if ($dry) {
            $this->dry[] = $migration->dry();

            return;
        }

        $migration->execute();

        $migration = Migration::where('name', $migration->getName())->fetch();

        if (count($migration) !== 1) {
            throw new Exception('This should never happen!');
        } else {
            $migration = $migration[0];
        }

        $migration->delete();
    }

    protected function runUp(ExecutableMigration $migration, bool $dry = false): void
    {
        $migration->up();

        if ($dry) {
            $this->dry[] = $migration->dry();

            return;
        }

        $migration->execute();

        Migration::create(['name' => $migration->getName()]);
    }

    protected function resolveMigrationObjects(): array
    {
        $this->loader->load(App::$app->getConfig('app.migrations.dir'));

        $migrations = [];

        foreach ($this->loader->classNames() as $migration) {
            $explode = explode('\\', $migration);

            $migrations[array_pop($explode)] = $this->resolver->resolve($migration);
        }

        return $migrations;
    }

    protected function createMigrationsTable(): void
    {
        $table = $this->database->create('migrations')->ifNotExists();

        $table->id();
        $table->string('name');

        $this->database->execute();
    }
}
