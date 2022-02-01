<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Response\Response;

interface IMiddleware
{
    public function getName(): string;

    /**
     * @param Request $request
     * @return Request|Response
     */
    public function __invoke(Request $request);
}
