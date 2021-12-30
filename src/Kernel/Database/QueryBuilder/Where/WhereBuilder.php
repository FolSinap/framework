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
                $expression = '';
                $end = '';
        }

        foreach ($this->wheres as $where) {
            $expression .= ' ' . $where->build();
        }

        return $expression . $end;
    }

    public function orWhere(string $field, $value, string $expression = '='): self
    {
        $this->wheres[] = new OrWhere($field, $value, $expression);

        return $this;
    }

    public function andWhere(string $field, $value, string $expression = '='): self
    {
        $this->wheres[] = new AndWhere($field, $value, $expression);

        return $this;
    }

    public function orNative(string $expression): self
    {
        $this->wheres[] = new OrExpression($expression);

        return $this;
    }

    public function andNative(string $expression): self
    {
        $this->wheres[] = new AndExpression($expression);

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
