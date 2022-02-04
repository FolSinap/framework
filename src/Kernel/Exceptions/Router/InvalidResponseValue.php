<?php

namespace FW\Kernel\Exceptions\Router;

use FW\Kernel\Response\Response;
use LogicException;

class InvalidResponseValue extends LogicException
{
    public function __construct($response)
    {
        $message = 'Returned value must be of type ' . Response::class . ' ' . gettype($response) . ' given';

        parent::__construct($message, 500);
    }
}