<?php

namespace FW\Kernel\View\TemplateEngine;

use BadFunctionCallException;
use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Exceptions\ExpressionParser\ParsingException;
use FW\Kernel\Exceptions\ExpressionParser\UndefinedKeyException;
use FW\Kernel\Exceptions\ExpressionParser\VariableParsingException;
use FW\Kernel\Exceptions\View\UnknownArgumentException;
use FW\Kernel\View\VariableContainer;

class ExpressionParser
{
    protected const ARRAY_SET_OPERATOR = '=>';
    protected const METHOD_CALL_OPERATOR = '->';
    protected const IF_OPERATORS = ['??', '?'];
    protected const OPERATORS = [
        '+', '-', '&&', '||', '??', '?', ':', '.', '*', '/', '%', '**', '==', '!=', '===', '!==', '<>','>', '>=', '<', '<=', '!'
    ];

    public function __construct(
        protected VariableContainer $container
    ) {
    }

    public function processExpression(string $expression): mixed
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
                    $variable = $this->getVariable($expression);

                    //todo: is this really necessary?
                    if ($variable instanceof Model) {
                        $variable->prepareForExport();
                    }

                    $expressions[$key] = $variable;
                } elseif (in_array($expression, self::IF_OPERATORS)) {
                    switch ($expression) {
                        case '??':
                            $expressions[$key] = eval('return ' . $expressions[$key - 1] . ';')
                                ?? $this->getVariable($expressions[$key + 1]);

                            unset($expressions[$key - 1], $expressions[$key + 1]);

                            break;
                        case '?':
                            $expressions[$key] = $expressions[$key - 1]
                                ? $this->getVariable($expressions[$key + 1])
                                : $this->getVariable($expressions[$key + 3]);

                            unset($expressions[$key - 1],
                                $expressions[$key + 1],
                                $expressions[$key + 2],
                                $expressions[$key + 3]
                            );

                            break;
                    }
                }
            }

            $storageArray = [];

            foreach ($expressions as $key => $expression) {
                //todo: operators are already processed. Is it necessary to use 'if' here?
                $storageArray[] = $this->isOperator($expression) ? $expression : '$expressions[' . $key . ']';
            }

            return eval('return ' . implode(' ', $storageArray) . ';');
        }
    }

    public function getVariable(string $variable): mixed
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

    protected function isOperator(string $expression): bool
    {
        return in_array($expression, self::OPERATORS);
    }

    protected function isArrayVar(string $var): bool
    {
        return str_contains($var, '[') && str_contains($var, ']');
    }

    protected function isCustomArrayVar(string $var): bool
    {
        return str_starts_with($var, '[') && str_ends_with($var, ']');
    }

    protected function isObjectExpression(string $expression): bool
    {
        return str_contains($expression, self::METHOD_CALL_OPERATOR);
    }

    protected function isNumericVar(string $var): bool
    {
        return is_numeric($var);
    }

    protected function isStringVar(string $var): bool
    {
        return str_ends_with($var, "'") && str_starts_with($var, "'")
            || str_ends_with($var, '"') && str_starts_with($var, '"');
    }

    protected function isBoolVar(string $var): bool
    {
        if(in_array(strtolower($var), ['true', 'false'])) {
            return true;
        }

        return false;
    }

    protected function isFunctionCall(string $expression): bool
    {
        return preg_match('/^[a-zA-z]{1,50}\((.|\n){0,50}\)/', $expression);
    }

    protected function callFunction(string $functionCall)
    {
        $functionName = $this->getFunctionName($functionCall);
        $args = $this->getFunctionArgs($functionCall);

        if (function_exists($functionName)) {
            return $functionName(...$args);
        }

        throw new BadFunctionCallException("Function $functionName does not exist.");
    }

    protected function getBoolVar(string $var): bool
    {
        if (!$this->isBoolVar($var)) {
            throw new VariableParsingException($var, 'bool');
        }

        if (strtolower($var) === 'true') {
            return true;
        }

        return false;
    }

    protected function getStringVar(string $var): string
    {
        if (!$this->isStringVar($var)) {
            throw new VariableParsingException($var, 'string');
        }

        return trim(trim($var, "'"), '"');
    }

    protected function getNumericVar(string $var)
    {
        if (!$this->isNumericVar($var)) {
            throw new VariableParsingException($var, 'numeric');
        }

        if (str_contains($var, '.')) {
            return (float) $var;
        }

        return (int) $var;
    }

    protected function parseObjectExpression(string $expression)
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

    protected function getFunctionName(string $functionCall)
    {
        if (!$this->isFunctionCall($functionCall)) {
            throw new VariableParsingException($functionCall, 'callable');
        }

        return explode('(', $functionCall)[0];
    }

    protected function getFunctionArgs(string $functionCall): array
    {
        if (!$this->isFunctionCall($functionCall)) {
            throw new VariableParsingException($functionCall, 'callable');
        }

        $args = get_string_between($functionCall, '(', ')');

        return $this->getFunctionArgsFromExpression($args);
    }

    protected function getArrayVar(string $expression)
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

    protected function createArray(string $expression): array
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
}
