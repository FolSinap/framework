<?php

namespace FW\Kernel\Exceptions;

use DomainException;
use Throwable;

class IllegalValueException extends DomainException
{
    public static function illegalValue(
        mixed $value,
        array $range,
        $code = 500,
        Throwable $previous = null,
        string $valueName = null
    ): static
    {
        $value = self::normalizeValue($value);

        $range = array_map(function (mixed $value) {
            return self::normalizeValue($value);
        }, $range);

        $message = (isset($valueName) ? ucfirst(strtolower($valueName)) : 'Value')
            . ' must be one of: ' . implode(', ', $range);

        $message .= ". \nGot $value instead.";

        return new static($message, $code, $previous);
    }

    public static function checkValue(mixed $value, array $range, string $subject = null): void
    {
        if (!in_array($value, $range)) {
            throw self::illegalValue($value, $range, valueName: $subject);
        }
    }

    protected static function normalizeValue(mixed $value): mixed
    {
        $value = self::normalizeArrayValue($value);
        $value = self::normalizeNullValue($value);

        return self::normalizeBoolValue($value);
    }

    private static function normalizeArrayValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return '['
                . implode(', ', array_map(function (mixed $item) {
                    return self::normalizeValue($item);
                }, $value))
                . ']';
        }

        return $value;
    }

    private static function normalizeBoolValue(mixed $value): mixed
    {
        if (is_bool($value) && $value) {
            return 'true';
        } elseif (is_bool($value) && !$value) {
            return 'false';
        }

        return $value;
    }

    private static function normalizeNullValue(mixed $value): mixed
    {
        if (is_null($value)) {
            return 'null';
        }

        return $value;
    }
}