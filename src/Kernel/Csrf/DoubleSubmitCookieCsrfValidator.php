<?php

namespace FW\Kernel\Csrf;

use FW\Kernel\Storage\Cookie;

class DoubleSubmitCookieCsrfValidator extends CsrfValidator
{
    protected const CSRF_TOKEN = 'csrf_token';

    protected Cookie $cookie;

    public function __construct(Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    public function generate(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->cookie->set(self::CSRF_TOKEN, $token, [
            'secure' => true,
            'httpOnly' => true,
            'expires' => time() + 15*60,
        ]);

        return $token;
    }

    public function isValid(string $csrfToken): bool
    {
        if (!$this->cookie->has(self::CSRF_TOKEN)) {
            return false;
        }

        $token = $this->cookie->get(self::CSRF_TOKEN);

        return hash_equals($token, $csrfToken);
    }
}
