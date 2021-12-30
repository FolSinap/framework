<?php

namespace Fwt\Framework\Kernel\Exceptions;

use DomainException;
use Throwable;

class IllegalValueException extends DomainException
{
    public function __construct($value, array $range, $code = 500, Throwable $previous = null)
    {
        $value = $this->normalizeValue($value);

        $message = 'Value must be one of: ';

        $lastKey = array_key_last($range);

        foreach ($range as $key => $item) {
            $message .= $key === $lastKey ? $this->normalizeValue($item) : $this->normalizeValue($item) . ', ';
        }

        $message .= ". \nGot $value instead.";

        parent::__construct($message, $code, $previous);
    }

    public static function checkValue($value, array $range): void
    {
        if (!in_array($value, $range)) {
            throw new self($value, $range);
        }
    }

    protected function normalizeValue($value)
    {
        $value = $this->normalizeArrayValue($value);
        $value = $this->normalizeNullValue($value);

        return $this->normalizeBoolValue($value);
    }

    protected function normalizeArrayValue($value)
    {
        if (is_array($value)) {
            $stringValue = '[';

            $lastKey = array_key_last($value);

            foreach ($value as $key => $item) {
                $stringValue .= $key === $lastKey ? $this->normalizeValue($item) : $this->normalizeValue($item) . ',';
            }

            return $stringValue . ']';
        }

        return $value;
    }

    protected function normalizeBoolValue($value)
    {
        if (is_bool($value) && $value) {
            return 'true';
        } elseif (is_bool($value) && !$value) {
            return 'false';
        }

        return $value;
    }

    protected function normalizeNullValue($value)
    {
        if (is_null($value)) {
            return 'null';
        }

        return $value;
    }
}