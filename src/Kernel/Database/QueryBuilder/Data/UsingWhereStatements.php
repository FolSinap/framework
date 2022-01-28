<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Data;

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

    public function andWhereAll(array $wheres): self
    {
        $field = array_key_first($wheres);
        $value = $wheres[$field];
        unset($wheres[$field]);

        $this->where($field, $value);

        foreach ($wheres as $field => $value) {
            $this->andWhere($field, $value);
        }

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $values = $this->fillParams($values);

        $this->whereBuilder = WhereBuilder::whereIn($field, $values);

        return $this;
    }

    public function andWhereInAll(array $wheres): self
    {
        $field = array_key_first($wheres);
        $value = $wheres[$field];
        unset($wheres[$field]);

        $this->whereIn($field, $value);

        foreach ($wheres as $field => $value) {
            $this->andWhereIn($field, $value);
        }

        return $this;
    }

    public function orWhereIn(string $field, array $values): self
    {
        $values = $this->fillParams($values);

        $this->whereBuilder->orWhereIn($field, $values);

        return $this;
    }

    public function andWhereIn(string $field, array $values): self
    {
        $values = $this->fillParams($values);

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

    protected function generateParamName(string $paramName): string
    {
        if (array_key_exists($paramName, $this->params)) {
            $chars = 'abcdefghijklmnopqrstuvwxyz';
            $chars = str_split($chars);
            $char = $chars[rand(array_key_first($chars), array_key_last($chars))];

            $paramName .= $char;
        }

        if (array_key_exists($paramName, $this->params)) {
            return $this->generateParamName($paramName);
        }

        return $paramName;
    }

    private function fillParams(array $values): array
    {
        foreach ($values as $key => $value) {
            $paramName = $this->generateParamName("param$key");
            $this->params[$paramName] = $value;
            $values[$key] = ":$paramName";
        }

        return $values;
    }
}
