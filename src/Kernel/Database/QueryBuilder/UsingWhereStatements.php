<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;

trait UsingWhereStatements
{
    protected WhereBuilder $whereBuilder;

    //todo: add andWhereIn(), orWhereIn(), whereIn() methods
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

    public function whereFromBuilder(WhereBuilder $where): self
    {
        $this->whereBuilder = $where;

        return $this;
    }

    protected function buildWhere(): string
    {
        return isset($this->whereBuilder) ? $this->whereBuilder->build() : '';
    }
}
