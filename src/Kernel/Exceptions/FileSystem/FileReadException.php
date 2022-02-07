<?php

namespace FW\Kernel\Exceptions\FileSystem;

use LogicException;

class FileReadException extends LogicException
{
    public function __construct(string $file)
    {
        parent::__construct(sprintf('Couldn\'t read file %s', $file));
    }
}
