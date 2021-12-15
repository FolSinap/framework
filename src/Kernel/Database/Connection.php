<?php

namespace Fwt\Framework\Kernel\Database;

use PDO;

class Connection
{
    protected string $dsn;
    protected ?string $user;
    protected ?string $password;
    protected PDO $pdo;

    public function __construct(string $dsn, string $user = null, string $password = null)
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
    }

    public function establish(): self
    {
        $this->pdo = new PDO($this->dsn, $this->user, $this->password);

        return $this;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
