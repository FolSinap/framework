<?php

namespace Fwt\Framework\Kernel\Csrf;

use Fwt\Framework\Kernel\App;

abstract class CsrfValidator
{
    public const TOKEN_KEY = '_token';

    abstract public function generate(): string;

    abstract public function isValid(string $csrfToken): bool;

    public static function getValidator(): self
    {
        $validator = App::$app->getConfig('app.csrf', SynchronizerCsrfValidator::class);

        return new $validator();
    }
}
