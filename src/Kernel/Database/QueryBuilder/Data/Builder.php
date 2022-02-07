<?php

namespace FW\Kernel\Database\QueryBuilder\Data;

abstract class Builder
{
    protected array $params = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = array_merge($this->params, $params);
    }
}
