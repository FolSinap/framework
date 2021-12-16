<?php

namespace Fwt\Framework\Kernel\Database;

use PDO;

class Database
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->establish();
    }

    public function select(string $from, array $columns = [])
    {
        $columns = empty($columns) ? '*' : '(' . implode(', ', $columns) . ')' ;
        $statement = $this->connection->createStatement("SELECT $columns FROM $from");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectClass(string $from, string $class, array $where = [], array $constructorArgs = [])
    {
        $columns = empty($columns) ? '*' : '(' . implode(', ', $columns) . ')' ;

        $expression = '';

        foreach ($where as $column => $value) {
            $expression .= "AND $column = $value";
        }

        $where = empty($where) ? '' : 'WHERE ' . $expression;
        $statement = $this->connection->createStatement("SELECT $columns FROM $from $where");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_CLASS, $class, $constructorArgs);
    }

    public function execute(string $sql): bool
    {
        return $this->connection->createStatement($sql)->execute();
    }
}
