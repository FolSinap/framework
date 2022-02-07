<?php

namespace FW\Kernel\Database\ORM\Casting;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use FW\Kernel\Exceptions\Casting\CastingException;
use FW\Kernel\Exceptions\IllegalValueException;

class Caster
{
    public const STRING = 'string';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const ARRAY = 'array';
    public const BOOL = 'bool';
    public const DATETIME = 'date::';
    public const JSON = 'json';
    public const PRIMITIVE_CASTS = [self::STRING, self::INT, self::FLOAT, self::ARRAY, self::BOOL];

    public function cast(string $value, string $to)
    {
        if (in_array($to, self::PRIMITIVE_CASTS)) {
            return $this->castPrimitive($value, $to);
        }

        if (class_exists($to)) {
            if (in_array(ICastable::class, class_implements($to))) {
                return $to::cast($value);
            } elseif (in_array(CarbonInterface::class, class_implements($to))) {
                return $this->toCarbon($value, $to);
            }

            throw new CastingException("Cannot cast to $to.");
        }

        if ($to === self::JSON) {
            return json_encode($value);
        }

        if (str_starts_with($to, self::DATETIME)) {
            $format = str_replace(self::DATETIME, '', $to);

            return $this->toCarbon($value)->format($format);
        }

        throw new CastingException("Cannot cast to $to.");
    }

    protected function toCarbon(string $value, string $carbonClass = null): CarbonInterface
    {
        $carbonClass = $carbonClass ?? Carbon::class;

        try {
            if (is_numeric($value)) {
                return $carbonClass::createFromTimestamp($value);
            }

            return $carbonClass::createFromTimeString($value);
        } catch (InvalidFormatException $exception) {
            throw new CastingException($exception->getMessage(), 500, $exception);
        }
    }

    protected function castPrimitive($value, string $to)
    {
        IllegalValueException::checkValue($to, self::PRIMITIVE_CASTS);

        switch ($to) {
            case self::ARRAY:
                return $this->toArray($value);
            default:
                settype($value, $to);

                return $value;
            }
    }

    protected function toArray($value): array
    {
        $decoded = json_decode($value, JSON_OBJECT_AS_ARRAY);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return (array) $value;
        }

        return (array) $decoded;
    }
}
