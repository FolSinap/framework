<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class AndExpression implements IExpressionBuilder
{
    protected Expression $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public static function where(string $field, $value, string $expression = '='): self
    {
        return new static(new Where($field, $value, $expression));
    }

    public static function whereIn(string $field, array $value): self
    {
        return new static(new WhereIn($field, $value));
    }

    public static function native(string $expression): self
    {
        return new static(new Expression($expression));
    }

    public function build(): string
    {
        return self::AND . ' ' . $this->expression->build();
    }
}
