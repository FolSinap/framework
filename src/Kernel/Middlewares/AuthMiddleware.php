<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Request;

class AuthMiddleware implements Middleware
{
    public function getName(): string
    {
        return 'authenticate';
    }

    public function pass(Request $request): Request
    {
        //write some code

        return $request;
    }
}
