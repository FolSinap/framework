<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Exception;
use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\Models\Migration;
use Fwt\Framework\Kernel\Database\Migration as ExecutableMigration;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use Fwt\Framework\Kernel\ObjectResolver;

class MigrationCommand extends Command
{
    protected Database $database;
    protected ObjectResolver $resolver;
    protected array $dry = [];

    public function __construct(Database $database, ObjectResolver $resolver)
    {
        $this->database = $database;
        $this->resolver = $resolver;
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
        $migrations = scandir(App::$app->getConfig('app.migrations.dir'));

        foreach ($migrations as $key => $migration) {
            if (in_array($migration, ['.', '..'])) {
                unset($migrations[$key]);

                continue;
            }

            $migrationName = str_replace('.php', '', $migration);

            $migrations[$migrationName] = $this->resolver->resolve(App::$app->getConfig('app.migrations.namespace') . "\\$migrationName");

            unset($migrations[$key]);
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
