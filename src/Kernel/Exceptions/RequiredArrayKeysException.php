<?php

namespace Fwt\Framework\Kernel\Exceptions;

use OutOfRangeException;

class RequiredArrayKeysException extends OutOfRangeException
{
    public function __construct(string $key, string $sprintf = null)
    {
        $message = sprintf($sprintf ?? 'Required key %s is not provided.', $key);

        parent::__construct($message);
    }

    public static function checkKeysExistence(array $keys, array $data, string $sprintf = null): void
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                continue;
            }

            throw new self($key, $sprintf);
        }
    }
}
