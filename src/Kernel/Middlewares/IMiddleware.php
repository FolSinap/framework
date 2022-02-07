<?php

namespace FW\Kernel\Middlewares;

use FW\Kernel\Request;
use FW\Kernel\Response\Response;

interface IMiddleware
{
    public function getName(): string;

    /**
     * @param Request $request
     * @return Request|Response
     */
    public function __invoke(Request $request);
}
