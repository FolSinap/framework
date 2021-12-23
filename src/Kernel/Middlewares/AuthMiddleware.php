<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Login\Authentication;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Response\Response;

class AuthMiddleware implements Middleware
{
    protected Authentication $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function getName(): string
    {
        return 'authenticate';
    }

    public function __invoke(Request $request)
    {
        if (!$this->auth->isAuthenticated()) {
            return Response::unauthorized();
        }

        return $request;
    }
}