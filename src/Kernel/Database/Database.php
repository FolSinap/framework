<?php

namespace Fwt\Framework\Kernel\Database;

use Fwt\Framework\Kernel\Database\QueryBuilder\DeleteBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\QueryBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\Schema\SchemaBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\SelectBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\UpdateBuilder;
use PDO;
use PDOStatement;

class Database
{
    protected Connection $connection;
    protected QueryBuilder $queryBuilder;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->establish();
        $this->queryBuilder = new QueryBuilder();
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

    public function insertMany(array $data, string $table): string
    {
        $this->queryBuilder->insertMany($table, $data);
        $this->execute();

        return $this->connection->getPdo()->lastInsertId();
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

    public function getStructureQueryBuilder(): SchemaBuilder
    {
        return SchemaBuilder::getBuilder();
    }

    public function executeQuery(string $sql, array $parameters = []): bool
    {
        return $this->connection->createStatement($sql)->execute($parameters);
    }

    public function execute(): PDOStatement
    {
        $statement = $this->connection->createStatement($this->queryBuilder->getQuery());
        $statement->execute($this->queryBuilder->getParams());

        $this->refresh();

        return $statement;
    }
}
