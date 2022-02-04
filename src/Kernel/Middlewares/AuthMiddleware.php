<?php

namespace FW\Kernel\Middlewares;

use FW\Kernel\Login\Authentication;
use FW\Kernel\Request;
use FW\Kernel\Response\Response;

class AuthMiddleware implements IMiddleware
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

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request)
    {
        if (!$this->auth->isAuthenticated()) {
            return Response::unauthorized();
        }

        return $request;
    }
}
