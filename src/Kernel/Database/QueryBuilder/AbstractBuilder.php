<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

abstract class AbstractBuilder implements Builder
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
