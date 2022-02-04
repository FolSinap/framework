<?php

namespace FW\Kernel\Database;

use FW\Kernel\Exceptions\Database\ConnectionException;
use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    protected string $dsn;
    protected ?string $user;
    protected ?string $password;
    protected PDO $pdo;

    public function __construct(array $config)
    {
        $db = $config['driver'];
        $drivers = $config['drivers'];
        $dbHost = $drivers[$db]['host'];
        $dbName = $drivers[$db]['name'];

        $this->dsn = "$db:dbname=$dbName;host=$dbHost";
        $this->user = $drivers[$db]['user'];
        $this->password = $drivers[$db]['password'];
    }

    public function establish(): self
    {
        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            throw new ConnectionException($exception->getMessage(), 500, $exception);
        }

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
