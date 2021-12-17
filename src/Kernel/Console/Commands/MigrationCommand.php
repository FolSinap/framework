<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\Models\Migration;
use Fwt\Framework\Kernel\Database\QueryBuilder\StructureQueryBuilder;
use Fwt\Framework\Kernel\ObjectResolver;

class MigrationCommand implements Command
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

    public function getRequiredParams(): array
    {
        return [];
    }

    public function execute(Input $input, Output $output): void
    {
        $sql = StructureQueryBuilder::getBuilder()
            ->create('migrations')
            ->ifNotExists()
            ->id()
            ->string('name')
            ->getQuery();

        $this->database->execute($sql);

        $executedMigrations = Migration::all();
        $executedMigrations = array_map(function ($migration) {
            return $migration->name;
        }, $executedMigrations);

        $migrations = scandir(App::$app->getProjectDir() . '/migrations');
        $numberOfExecutions = 0;

        foreach ($migrations as $migration) {
            if (in_array($migration, ['.', '..'])) {
                continue;
            }

            $migrationName = rtrim($migration, '.php');

            if (!in_array($migrationName, $executedMigrations)) {
                $migration = $this->resolver->resolve('\\App\\Migrations\\' . $migrationName);

                $migration->up();

                Migration::create(['name' => $migrationName]);

                $numberOfExecutions++;
            }
        }

        $output->success($numberOfExecutions . ' migrations were executed.');
    }
}
