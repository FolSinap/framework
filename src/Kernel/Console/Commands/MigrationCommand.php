<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\Models\Migration;
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
        $sql = 'CREATE TABLE IF NOT EXISTS migrations (id BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR (255) NOT NULL, PRIMARY KEY (id))';

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

                $sql = "INSERT INTO migrations (name) VALUES ('$migrationName')";

                $this->database->execute($sql);

                $numberOfExecutions++;
            }
        }

        $output->success($numberOfExecutions . ' migrations were executed.');
    }
}
