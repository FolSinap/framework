<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Login\Authentication;
use Fwt\Framework\Kernel\Request;

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

    public function __invoke(Request $request): Request
    {
        if (!$this->auth->isAuthenticated()) {
            throw new \Exception('un');
        }

        return $request;
    }
}
