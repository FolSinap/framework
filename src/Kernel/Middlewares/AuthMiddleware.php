<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Request;

class AuthMiddleware implements Middleware
{
    public function getName(): string
    {
        return 'authenticate';
    }

    public function __invoke(Request $request): Request
    {
        //write some code
dd('asdasd');
        return $request;
    }
}
