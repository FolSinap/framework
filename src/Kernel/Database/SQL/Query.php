<?php

namespace FW\Kernel\Database\SQL;

class Query
{
    protected string $query;
    protected array $params;

    public function __construct(string $query, array $params = [])
    {
        $this->query = $query;
        $this->params = $params;
    }

    public function __toString(): string
    {
        return $this->getQuery();
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
