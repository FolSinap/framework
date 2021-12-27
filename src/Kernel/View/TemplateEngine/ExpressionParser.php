<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\Exceptions\ExpressionParser\UndefinedKeyException;
use Fwt\Framework\Kernel\Exceptions\ExpressionParser\VariableParsingException;
use Fwt\Framework\Kernel\Exceptions\View\UnknownArgumentException;
use Fwt\Framework\Kernel\View\VariableContainer;

class ExpressionParser
{
    protected const OPERATORS = [
        '+', '-', '&&', '||', '??', '.', '*', '/', '%', '**', '==', '!=', '===', '!==', '<>','>', '>=', '<', '<=', '!',
    ];
    protected VariableContainer $container;

    public function __construct(VariableContainer $container)
    {
        $this->container = $container;
    }

    public function processExpression(string $expression)
    {
        if ($this->isStringVar($expression)) {
            return $this->getStringVar($expression);
        }

        $expressions = explode(' ', $expression);

        if (count($expressions) === 1) {
            return $this->getVariable($expressions[0]);
        } else {
            foreach ($expressions as $key => $expression) {
                if (!$this->isOperator($expression)) {
                    $expressions[$key] = var_export($this->getVariable($expression), true);
                }
            }

            return eval('return ' . implode(' ', $expressions) . ';');
        }
    }

    public function getVariable(string $variable)
    {
        switch (true) {
            case $this->isArrayVar($variable):
                return $this->getArrayVar($variable);
            case $this->isStringVar($variable):
                return $this->getStringVar($variable);
            case $this->isBoolVar($variable):
                return $this->getBoolVar($variable);
            case $this->isNumericVar($variable):
                return $this->getNumericVar($variable);
            default:
                if (!isset($this->container[$variable])) {
                    throw new UnknownArgumentException($variable);
                }

                return  $this->container[$variable];
        }
    }

    public function isOperator(string $expression): bool
    {
        return in_array($expression, self::OPERATORS);
    }

    public function isArrayVar(string $var): bool
    {
        return str_contains($var, '[') && str_contains($var, ']');
    }

    public function isNumericVar(string $var): bool
    {
        return is_numeric($var);
    }

    public function isStringVar(string $var): bool
    {
        return str_ends_with($var, "'") && str_starts_with($var, "'")
            || str_ends_with($var, '"') && str_starts_with($var, '"');
    }

    public function isBoolVar(string $var): bool
    {
        if(in_array(strtolower($var), ['true', 'false'])) {
            return true;
        }

        return false;
    }

    public function getBoolVar(string $var): bool
    {
        if (!$this->isBoolVar($var)) {
            throw new VariableParsingException($var, 'bool');
        }

        if (strtolower($var) === 'true') {
            return true;
        }

        return false;
    }

    public function getStringVar(string $var): string
    {
        if (!$this->isStringVar($var)) {
            throw new VariableParsingException($var, 'string');
        }

        return trim(trim($var, "'"), '"');
    }

    public function getNumericVar(string $var)
    {
        if (!$this->isNumericVar($var)) {
            throw new VariableParsingException($var, 'numeric');
        }

        if (str_contains($var, '.')) {
            return (float) $var;
        }

        return (int) $var;
    }

    public function getArrayVar(string $expression)
    {
        $explode = explode('[', $expression);
        $keys = [];

        if (count($explode) > 1) {
            if ($explode[0] === '') {
                return $this->createArray($expression);
            }

            array_shift($explode);

            foreach ($explode as $key) {
                $keys[] = trim($key, ']');
            }
        } else {
            throw new VariableParsingException($expression, 'array');
        }

        $argName = str_replace('[' . implode('][', $keys) . ']', '', $expression);

        if (!isset($this->container[$argName])) {
            throw new UndefinedKeyException($argName);
        }

        $variable = $this->container[$argName];

        foreach ($keys as $key) {
            $key = is_numeric($key) ? (int) $key : $key;

            if (!isset($variable[$key])) {
                throw new UndefinedKeyException($key);
            }

            $variable = $variable[$key];
        }

        return $variable;
    }

    public function createArray(string $expression): array
    {
        if (!$this->isArrayVar($expression)) {
            throw new VariableParsingException($expression, 'array');
        }

        $values = rtrim(ltrim($expression, '['), ']');
        $values = str_replace(' ', '', $values);
        $values = explode(',', $values);

        $array = [];

        foreach ($values as $value) {
            $array[] = $this->getVariable($value);
        }

        return $array;
    }

    public function getVariables(): VariableContainer
    {
        return $this->container;
    }
}
