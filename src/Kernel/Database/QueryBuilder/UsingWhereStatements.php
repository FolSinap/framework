<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;

trait UsingWhereStatements
{
    protected WhereBuilder $whereBuilder;

    public function where(string $field, string $value, string $expression = '='): self
    {
        $this->whereBuilder = WhereBuilder::where($field, ":$field", $expression);
        $this->params[$field] = $value;

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        foreach ($values as $key => $value) {
            $paramName = "param$key";
            $this->params[$paramName] = $value;
            $values[$key] = ":$paramName";
        }

        $this->whereBuilder = WhereBuilder::whereIn($field, $values);

        return $this;
    }

    public function orWhereIn(string $field, array $values): self
    {
        foreach ($values as $key => $value) {
            $paramName = "param$key";
            $this->params[$paramName] = $value;
            $values[$key] = ":$paramName";
        }

        $this->whereBuilder->orWhereIn($field, $values);

        return $this;
    }

    public function andWhereIn(string $field, array $values): self
    {
        foreach ($values as $key => $value) {
            $paramName = "param$key";
            $this->params[$paramName] = $value;
            $values[$key] = ":$paramName";
        }

        $this->whereBuilder->andWhereIn($field, $values);

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

    protected function buildWhere(): string
    {
        return isset($this->whereBuilder) ? $this->whereBuilder->build() : '';
    }
}
