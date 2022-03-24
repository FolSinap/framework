<?php

namespace FW\Kernel\Middlewares;

use FW\Kernel\Csrf\CsrfValidator;
use FW\Kernel\Exceptions\Config\InvalidConfigTypeException;
use FW\Kernel\Exceptions\Csrf\InvalidCsrfTokenException;
use FW\Kernel\Request;
use FW\Kernel\Routing\Route;

class ValidateCsrfMiddleware implements IMiddleware
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getName(): string
    {
        return 'csrf';
    }

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request)
    {
        //todo: check for X-CSRF-Token in header
        if (!in_array($this->request->getMethod(), Route::DANGEROUS_METHODS)) {
            return $request;
        }

        if (!$this->validateCsrf()) {
            throw new InvalidCsrfTokenException();
        }

        return $request;
    }

    protected function validateCsrf(): bool
    {
        $isEnabled = config('app.csrf.enable', false) ?? false;

        if (!is_bool($isEnabled)) {
            throw new InvalidConfigTypeException($isEnabled, ['bool']);
        }

        if (!$isEnabled) {
            return true;
        }

        if (array_key_exists(CsrfValidator::TOKEN_KEY, $this->request->getBodyParameters())) {
            return CsrfValidator::getValidator()->isValid($this->request->getBodyParameters()[CsrfValidator::TOKEN_KEY]);
        }

        return false;
    }
}
