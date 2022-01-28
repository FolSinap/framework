<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class TypeRule implements IRule
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';
    public const TYPE_BOOL = 'bool';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NULL = 'null';
    public const BASIC_TYPES = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_INTEGER,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
        self::TYPE_ARRAY,
        self::TYPE_OBJECT,
        self::TYPE_BOOL,
        self::TYPE_BOOLEAN,
        self::TYPE_NULL
    ];

    protected array $allowedTypes;
    protected string $errorMessage = 'Invalid value type.';

    public function __construct($allowedTypes, string $errorMessage = null)
    {
        if (!is_array($allowedTypes)) {
            $allowedTypes = [$allowedTypes];
        }

        foreach ($allowedTypes as $type) {
            if (!class_exists($type) && !in_array($type, self::BASIC_TYPES)) {
                throw new IllegalValueException($type, self::BASIC_TYPES);
            }
        }

        if ($errorMessage) {
            $this->errorMessage = $errorMessage;
        }

        $this->allowedTypes = $allowedTypes;
    }

    public function validate($value): bool
    {
        $allowedTypes = $this->allowedTypes;

        foreach ($allowedTypes as $type) {
            $type = self::TYPE_BOOLEAN === $type ? self::TYPE_BOOL : $type;
            $isFunction = 'is_'.$type;
            $ctypeFunction = 'ctype_'.$type;
            if (function_exists($isFunction) && $isFunction($value)) {
                return true;
            } elseif (function_exists($ctypeFunction) && $ctypeFunction($value)) {
                return true;
            } elseif ($value instanceof $type) {
                return true;
            }
        }

        return false;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
