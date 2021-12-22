<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Request;

interface Middleware
{
    public function getName(): string;

    public function __invoke(Request $request): Request;
}
