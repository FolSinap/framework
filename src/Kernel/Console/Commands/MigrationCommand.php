<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Exception;
use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\Models\Migration;
use Fwt\Framework\Kernel\Database\Migration as ExecutableMigration;
use Fwt\Framework\Kernel\Database\QueryBuilder\StructureQueryBuilder;
use Fwt\Framework\Kernel\ObjectResolver;

class MigrationCommand extends AbstractCommand
{
    protected Database $database;
    protected ObjectResolver $resolver;

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

    public function getOptionalOptions(): array
    {
        return [
            'down' => ['Run down all migrations', 'd'],
            'back' => ['Rollback last migration', 'b'],
        ];
    }

    public function execute(Input $input, Output $output): void
    {
        $this->createMigrationsTable();

        $executedMigrations = Migration::all();
        $executedMigrations = array_map(function ($migration) {
            return $migration->name;
        }, $executedMigrations);

        $down = $input->getOption('down', 'd');
        $back = $input->getOption('back', 'b');

        $migrations = $this->resolveMigrationObjects();

        $numberOfExecutions = 0;

        if ($back) {
            if ($last = array_pop($executedMigrations)) {
                $this->runDown($migrations[$last]);

                $numberOfExecutions++;
            }
        } else {
            foreach ($migrations as $migration) {
                if ($down && in_array($migration->getName(), $executedMigrations)) {
                    $this->runDown($migration);

                    $numberOfExecutions++;
                } elseif (!$down && !in_array($migration->getName(), $executedMigrations)) {
                    $this->runUp($migration);

                    $numberOfExecutions++;
                }
            }
        }

        $output->success($numberOfExecutions . ' migrations were executed.');
    }

    protected function runDown(ExecutableMigration $migration): void
    {
        $migration->down();

        $migration = Migration::where(['name' => $migration->getName()]);

        if (count($migration) !== 1) {
            throw new Exception('This should never happen!');
        } else {
            $migration = $migration[0];
        }

        $migration->delete();
    }

    protected function runUp(ExecutableMigration $migration): void
    {
        $migration->up();

        Migration::create(['name' => $migration->getName()]);
    }

    protected function resolveMigrationObjects(): array
    {
        $migrations = scandir(App::$app->getConfig()->get('app.migrations.dir'));

        foreach ($migrations as $key => $migration) {
            if (in_array($migration, ['.', '..'])) {
                unset($migrations[$key]);

                continue;
            }

            $migrationName = str_replace('.php', '', $migration);

            $migrations[$migrationName] = $this->resolver->resolve(App::$app->getConfig()->get('app.migrations.namespace') . "\\$migrationName");

            unset($migrations[$key]);
        }

        return $migrations;
    }

    protected function createMigrationsTable(): void
    {
        $sql = StructureQueryBuilder::getBuilder()
            ->create('migrations')
            ->ifNotExists()
            ->id()
            ->string('name')
            ->getQuery();

        $this->database->execute($sql);
    }
}
