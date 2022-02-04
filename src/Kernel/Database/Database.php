<?php

namespace FW\Kernel\Database;

use FW\Kernel\Database\QueryBuilder\Data\DeleteBuilder;
use FW\Kernel\Database\QueryBuilder\Data\SelectBuilder;
use FW\Kernel\Database\QueryBuilder\Data\UpdateBuilder;
use FW\Kernel\Database\QueryBuilder\QueryBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableAlterer;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableBuilder;
use FW\Kernel\Database\QueryBuilder\Schema\Tables\TableDropper;
use FW\Kernel\Database\SQL\Query;
use FW\Kernel\Database\SQL\SqlLogger;
use PDO;
use PDOStatement;

class Database
{
    protected Connection $connection;
    protected QueryBuilder $queryBuilder;
    protected SqlLogger $logger;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->establish();
        $this->queryBuilder = new QueryBuilder();
        $this->logger = SqlLogger::getLogger();
    }

    public function update(string $table, array $data): UpdateBuilder
    {
        return $this->queryBuilder->update($table, $data);
    }

    public function select(string $from, array $columns = []): SelectBuilder
    {
        return $this->queryBuilder->select($from, $columns);
    }

    public function delete(string $from): DeleteBuilder
    {
        return $this->queryBuilder->delete($from);
    }

    public function insert(array $data, string $table): string
    {
        $this->queryBuilder->insert($table, $data);
        $this->execute();

        return $this->connection->getPdo()->lastInsertId();
    }

    public function describe(string $table): array
    {
        //todo: only for MySQL
        $description = $this->executeNative("DESCRIBE $table;")->fetchAll(PDO::FETCH_ASSOC);

        $columns = [];

        foreach ($description as $column) {
            $columns[] = $column['Field'];
        }

        return $columns;
    }

    public function insertMany(array $data, string $table): string
    {
        $this->queryBuilder->insertMany($table, $data);
        $this->execute();

        return $this->connection->getPdo()->lastInsertId();
    }

    public function create(string $table): TableBuilder
    {
        return $this->queryBuilder->create($table);
    }

    public function drop(string $table): TableDropper
    {
        return $this->queryBuilder->drop($table);
    }

    public function alter(string $table): TableAlterer
    {
        return $this->queryBuilder->alter($table);
    }

    public function populateObject(object $object): ?object
    {
        $statement = $this->execute();

        $statement->setFetchMode(PDO::FETCH_INTO, $object);
        $fetched = $statement->fetch(PDO::FETCH_INTO);

        return $fetched === false ? null : $fetched;
    }

    public function fetchAsObject(string $class, array $constructorArgs = []): array
    {
        $statement = $this->execute();

        return $statement->fetchAll(PDO::FETCH_CLASS, $class, $constructorArgs);
    }

    public function fetchAssoc(): array
    {
        $statement = $this->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function refresh(): void
    {
        $this->queryBuilder = new QueryBuilder();
    }

    public function executeNative(string $sql, array $parameters = []): PDOStatement
    {
        $query = new Query($sql, $parameters);

        return $this->executeQuery($query);
    }

    public function executeQuery(Query $query): PDOStatement
    {
        $this->logger->log($query);

        $statement = $this->connection->createStatement($query->getQuery());
        $statement->execute($query->getParams());

        return $statement;
    }

    public function execute(): PDOStatement
    {
        $query = $this->queryBuilder->getQuery();
        $this->logger->log($query);

        $statement = $this->connection->createStatement($query);
        $statement->execute($query->getParams());

        $this->refresh();

        return $statement;
    }
}
