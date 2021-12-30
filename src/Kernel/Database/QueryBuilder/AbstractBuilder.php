<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;

abstract class AbstractBuilder implements Builder
{
    protected WhereBuilder $whereBuilder;
    protected array $params = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = array_merge($this->params, $params);
    }

    public function where(string $field, string $value, string $expression = '='): self
    {
        $this->whereBuilder = WhereBuilder::where($field, ":$field", $expression);
        $this->params[$field] = $value;

        return $this;
    }

    public function andWhere(string $field, string $value, string $expression = '='): self
    {
        $this->whereBuilder->andWhere($field, ":$field", $expression);
        $this->params[$field] = $value;

        return $this;
    }

    public function orWhere(string $field, string $value, string $expression = '='): self
    {
        $this->whereBuilder->orWhere($field, ":$field", $expression);
        $this->params[$field] = $value;

        return $this;
    }

    public function nativeWhere(string $expression): self
    {
        $this->whereBuilder = WhereBuilder::native($expression);

        return $this;
    }

    public function andNative(string $expression): self
    {
        $this->whereBuilder->andNative($expression);

        return $this;
    }

    public function orNative(string $expression): self
    {
        $this->whereBuilder->orNative($expression);

        return $this;
    }

    public function getWhereBuilder(): WhereBuilder
    {
        return $this->whereBuilder;
    }

    protected function buildWhere(): string
    {
        return isset($this->whereBuilder) ? $this->whereBuilder->build() : '';
    }
}
