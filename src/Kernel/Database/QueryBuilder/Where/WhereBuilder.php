<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class WhereBuilder implements ExpressionBuilder
{
    /** @var ExpressionBuilder[] $wheres */
    protected array $wheres;
    protected ?string $quantifier;

    public function __construct(ExpressionBuilder $where, string $quantifier = null)
    {
        IllegalValueException::checkValue($quantifier, [null, self::OR, self::AND]);

        $this->wheres[] = $where;
        $this->quantifier = $quantifier;
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
        switch ($this->quantifier) {
            case self::OR:
                $expression = self::OR . ' (';
                $end = ')';
                break;
            case self::AND:
                $expression = self::AND . ' (';
                $end = ')';
                break;
            default:
                $expression = ' WHERE';
                $end = '';
        }

        foreach ($this->wheres as $where) {
            $expression .= ' ' . $where->build();
        }

        return $expression . $end;
    }

    public function orWhere(string $field, $value, string $expression = '='): self
    {
        $this->wheres[] = OrExpression::where($field, $value, $expression);

        return $this;
    }

    public function andWhere(string $field, $value, string $expression = '='): self
    {
        $this->wheres[] = AndExpression::where($field, $value, $expression);

        return $this;
    }

    public function orWhereIn(string $field, array $value): self
    {
        $this->wheres[] = OrExpression::whereIn($field, $value);

        return $this;
    }

    public function andWhereIn(string $field, array $value): self
    {
        $this->wheres[] = AndExpression::whereIn($field, $value);

        return $this;
    }

    public function orNative(string $expression): self
    {
        $this->wheres[] = OrExpression::native($expression);

        return $this;
    }

    public function andNative(string $expression): self
    {
        $this->wheres[] = AndExpression::native($expression);

        return $this;
    }

    public function or(self $where): self
    {
        $this->wheres[] = $where->setQuantifier(self::OR);

        return $this;
    }

    public function and(self $where): self
    {
        $this->wheres[] = $where->setQuantifier(self::AND);

        return $this;
    }

    protected function setQuantifier(string $quantifier): self
    {
        $this->quantifier = $quantifier;

        return $this;
    }
}
