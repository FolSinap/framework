<?php

namespace Fwt\Framework\Kernel\Database;

use Fwt\Framework\Kernel\Database\QueryBuilder\QueryBuilder;
use Fwt\Framework\Kernel\Database\QueryBuilder\StructureQueryBuilder;
use PDO;

class Database
{
    protected Connection $connection;
    protected QueryBuilder $queryBuilder;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->establish();
        $this->queryBuilder = new QueryBuilder();
    }

    public function select(array $columns = []): QueryBuilder
    {
        return $this->queryBuilder->select($columns);
    }

    public function insert(array $data, string $table): bool
    {
        $return = $this->execute($this->queryBuilder->insert($data, $table)->getQuery(), $this->queryBuilder->getParams());
        $this->refresh();

        return $return;
    }

    public function fetchAsObject(string $class, array $constructorArgs = []): array
    {
        $statement = $this->connection->createStatement($this->queryBuilder->getQuery());
        $statement->execute($this->queryBuilder->getParams());
        $this->refresh();

        return $statement->fetchAll(PDO::FETCH_CLASS, $class, $constructorArgs);
    }

    public function fetchAssoc(): array
    {
        $statement = $this->connection->createStatement($this->queryBuilder->getQuery());
        $statement->execute($this->queryBuilder->getParams());
        $this->refresh();

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

    public function getStructureQueryBuilder(): StructureQueryBuilder
    {
        return StructureQueryBuilder::getBuilder();
    }

    public function execute(string $sql, array $parameters = []): bool
    {
        return $this->connection->createStatement($sql)->execute($parameters);
    }
}
