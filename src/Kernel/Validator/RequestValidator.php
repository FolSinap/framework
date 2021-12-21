<?php

namespace Fwt\Framework\Kernel\Validator;

use Fwt\Framework\Kernel\Request;

abstract class RequestValidator extends Validator
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        parent::__construct($this->getRules());
    }

    abstract public function getRules(): array;

    public function validate(): bool
    {
        return parent::validateData($this->getBodyData());
    }

    public function getBodyData(): array
    {
        return $this->request->getBodyParameters();
    }

    public function getQueryData(): array
    {
        return $this->request->getQueryParameters();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
