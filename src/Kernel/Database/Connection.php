<?php

namespace Fwt\Framework\Kernel\Database;

use PDO;
use PDOStatement;

class Connection
{
    protected string $dsn;
    protected ?string $user;
    protected ?string $password;
    protected PDO $pdo;

    public function __construct(array $dbConfig)
    {
        $db = $dbConfig['driver'];
        $dbHost = $dbConfig['host'];
        $dbName = $dbConfig['name'];

        $this->dsn = "$db:dbname=$dbName;host=$dbHost";
        $this->user = $dbConfig['user'];
        $this->password = $dbConfig['password'];
    }

    public function establish(): self
    {
        $this->pdo = new PDO($this->dsn, $this->user, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    public function createStatement(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
