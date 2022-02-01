<?php

namespace Fwt\Framework\Kernel\Middlewares;

use Fwt\Framework\Kernel\Csrf\CsrfValidator;
use Fwt\Framework\Kernel\Exceptions\Config\InvalidConfigTypeException;
use Fwt\Framework\Kernel\Exceptions\Csrf\InvalidCsrfTokenException;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Routing\Route;

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
        $isEnabled = config('app.csrf.enable', false);

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
