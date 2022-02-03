<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class Where extends Expression
{
    protected const WHERE_EXPRESSIONS = ['!=', '<>', '=', '>', '<', '>=', '<=', 'LIKE'];

    protected string $field;
    protected string $value;

    public function __construct(string $field, string $value, string $expression = '=')
    {
        IllegalValueException::checkValue($expression, static::WHERE_EXPRESSIONS);

        $this->field = $field;
        $this->value = $value;

        parent::__construct($expression);
    }

    public function build(): string
    {
        return "$this->field $this->expression $this->value";
    }
}
