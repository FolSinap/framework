<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use BadFunctionCallException;
use Fwt\Framework\Kernel\Exceptions\ExpressionParser\ParsingException;
use Fwt\Framework\Kernel\Exceptions\ExpressionParser\UndefinedKeyException;
use Fwt\Framework\Kernel\Exceptions\ExpressionParser\VariableParsingException;
use Fwt\Framework\Kernel\Exceptions\View\UnknownArgumentException;
use Fwt\Framework\Kernel\View\VariableContainer;

class ExpressionParser
{
    protected const ARRAY_SET_OPERATOR = '=>';
    protected const METHOD_CALL_OPERATOR = '->';
    protected const IF_OPERATORS = ['??', '?'];
    protected const OPERATORS = [
        '+', '-', '&&', '||', '??', '?', ':', '.', '*', '/', '%', '**', '==', '!=', '===', '!==', '<>','>', '>=', '<', '<=', '!'
    ];
    protected VariableContainer $container;

    public function __construct(VariableContainer $container)
    {
        $this->container = $container;
    }

    public function processExpression(string $expression)
    {
        $expression = trim($expression, ' ');

        if ($this->isStringVar($expression)) {
            return $this->getStringVar($expression);
        } elseif ($this->isCustomArrayVar($expression)) {
            return $this->getArrayVar($expression);
        } elseif ($this->isFunctionCall($expression)) {
            return $this->callFunction($expression);
        }

        $expressions = explode(' ', $expression);

        if (count($expressions) === 1) {
            return $this->getVariable($expressions[0]);
        } else {
            for ($i = 0; $i < count($expressions); $i++) {
                $key = array_keys($expressions)[$i];
                $expression = $expressions[$key];

                if (!$this->isOperator($expression)) {
                    $expressions[$key] = var_export($this->getVariable($expression), true);
                } elseif (in_array($expression, self::IF_OPERATORS)) {
                    switch ($expression) {
                        case '??':
                            $expressions[$key] = var_export(eval('return ' . $expressions[$key - 1] . ';'), true)
                                ?? $this->getVariable($expressions[$key + 1]);

                            unset($expressions[$key - 1], $expressions[$key + 1]);

                            break;
                        case '?':
                            $expressions[$key] = eval('return ' . $expressions[$key - 1] . ';')
                                ? var_export($this->getVariable($expressions[$key + 1]), true)
                                : var_export($this->getVariable($expressions[$key + 3]), true);

                            unset($expressions[$key - 1],
                                $expressions[$key + 1],
                                $expressions[$key + 2],
                                $expressions[$key + 3]
                            );

                            break;
                    }
                }
            }

            return eval('return ' . implode(' ', $expressions) . ';');
        }
    }

    public function getVariable(string $variable)
    {
        switch (true) {
            case $this->isStringVar($variable):
                return $this->getStringVar($variable);
            case $this->isFunctionCall($variable):
                return $this->callFunction($variable);
            case $this->isObjectExpression($variable):
                return $this->parseObjectExpression($variable);
            case $this->isArrayVar($variable):
                return $this->getArrayVar($variable);
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

    public function isCustomArrayVar(string $var): bool
    {
        return str_starts_with($var, '[') && str_ends_with($var, ']');
    }

    public function isObjectExpression(string $expression): bool
    {
        return str_contains($expression, self::METHOD_CALL_OPERATOR);
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

    public function isFunctionCall(string $expression): bool
    {
        return preg_match('/^[a-zA-z]{1,50}\((.|\n){0,50}\)/', $expression);
    }

    public function callFunction(string $functionCall)
    {
        $functionName = $this->getFunctionName($functionCall);
        $args = $this->getFunctionArgs($functionCall);

        if (function_exists($functionName)) {
            return $functionName(...$args);
        }

        throw new BadFunctionCallException("Function $functionName does not exist.");
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

    public function parseObjectExpression(string $expression)
    {
        if (!$this->isObjectExpression($expression)) {
            throw new VariableParsingException($expression, 'object');
        }

        $parts = explode(self::METHOD_CALL_OPERATOR, $expression);
        $object = $this->getVariable($parts[0]);
        unset($parts[0]);

        foreach ($parts as $method) {
            if ($this->isFunctionCall($method)) {
                $methodName = $this->getFunctionName($method);
                $args = $this->getFunctionArgs($method);

                $object = $object->$methodName(...$args);
            } else {
                $object = $object->$method;
            }
        }

        return $object;
    }

    public function getFunctionName(string $functionCall)
    {
        if (!$this->isFunctionCall($functionCall)) {
            throw new VariableParsingException($functionCall, 'callable');
        }

        return explode('(', $functionCall)[0];
    }

    public function getFunctionArgs(string $functionCall): array
    {
        if (!$this->isFunctionCall($functionCall)) {
            throw new VariableParsingException($functionCall, 'callable');
        }

        $args = get_string_between($functionCall, '(', ')');

        return $this->getFunctionArgsFromExpression($args);
    }

    public function getFunctionArgsFromExpression(string $argsExpression): array
    {
        if ($argsExpression === '') {
            return [];
        }

        $args = explode(',', $argsExpression);

        foreach ($args as $position => $arg) {
            $args[$position] = $this->processExpression($arg);
        }

        return $args;
    }

    public function getArrayVar(string $expression)
    {
        $expression = trim($expression, ' ');
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
            if (str_contains($value, self::ARRAY_SET_OPERATOR)) {
                $keyValue = explode(self::ARRAY_SET_OPERATOR, $value);

                if (count($keyValue) !== 2) {
                    throw ParsingException::invalidArrayDefinition(
                        'Array definition must contain only one ' . self::ARRAY_SET_OPERATOR . ' element'
                    );
                }

                [$key, $value] = $keyValue;

                $array[$this->getVariable($key)] = $this->getVariable($value);
            } else {
                $array[] = $this->getVariable($value);
            }
        }

        return $array;
    }

    public function getVariables(): VariableContainer
    {
        return $this->container;
    }
}
